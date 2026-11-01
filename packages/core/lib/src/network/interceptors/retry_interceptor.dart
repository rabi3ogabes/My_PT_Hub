import 'dart:async';
import 'dart:math';

import 'package:dio/dio.dart';

/// Request option `extra` keys understood by [RetryInterceptor].
abstract final class RetryExtra {
  /// Force-enable retries for a non-idempotent method (e.g. a POST carrying a
  /// client ULID or an Idempotency-Key, which the server de-duplicates).
  static const retryable = 'au.retryable';

  /// Disable retries for this request.
  static const noRetry = 'au.noRetry';
  static const _attempt = 'au.retryAttempt';
}

/// Retries transient failures with exponential backoff and full jitter.
///
/// Retried: connection errors, timeouts, 408/429/502/503/504. GET/HEAD/PUT/
/// DELETE/OPTIONS are retried by default; POST/PATCH only when the request is
/// idempotent (`Idempotency-Key` header or [RetryExtra.retryable]).
class RetryInterceptor extends Interceptor {
  RetryInterceptor({
    required this.dio,
    this.maxRetries = 3,
    this.baseDelay = const Duration(milliseconds: 400),
    this.maxDelay = const Duration(seconds: 8),
    Random? random,
    Future<void> Function(Duration)? sleep,
  })  : _random = random ?? Random(),
        _sleep = sleep ?? ((d) => Future<void>.delayed(d));

  final Dio dio;
  final int maxRetries;
  final Duration baseDelay;
  final Duration maxDelay;
  final Random _random;
  final Future<void> Function(Duration) _sleep;

  static const _idempotentMethods = {'GET', 'HEAD', 'PUT', 'DELETE', 'OPTIONS'};
  static const _retryStatuses = {408, 429, 502, 503, 504};

  @override
  Future<void> onError(DioException err, ErrorInterceptorHandler handler) async {
    final options = err.requestOptions;
    final attempt = (options.extra[RetryExtra._attempt] as int?) ?? 0;
    if (attempt >= maxRetries || !_shouldRetry(err)) return handler.next(err);

    await _sleep(delayFor(attempt, retryAfter: _retryAfter(err.response)));
    options.extra[RetryExtra._attempt] = attempt + 1;
    try {
      final response = await dio.fetch<dynamic>(options);
      handler.resolve(response);
    } on DioException catch (e) {
      handler.next(e);
    }
  }

  /// Backoff for the given zero-based [attempt]: `base * 2^attempt` capped at
  /// [maxDelay], with full jitter; a server `Retry-After` wins when present.
  Duration delayFor(int attempt, {Duration? retryAfter}) {
    if (retryAfter != null) return retryAfter > maxDelay ? maxDelay : retryAfter;
    final exp = baseDelay.inMilliseconds * pow(2, attempt);
    final capped = min(exp.toDouble(), maxDelay.inMilliseconds.toDouble());
    final jittered = capped / 2 + _random.nextDouble() * capped / 2;
    return Duration(milliseconds: jittered.round());
  }

  bool _shouldRetry(DioException err) {
    final options = err.requestOptions;
    if (options.extra[RetryExtra.noRetry] == true) return false;
    if (options.data is FormData) return false; // multipart streams cannot be replayed
    final idempotent = _idempotentMethods.contains(options.method.toUpperCase()) ||
        options.extra[RetryExtra.retryable] == true ||
        options.headers.containsKey('Idempotency-Key');
    if (!idempotent) return false;
    switch (err.type) {
      case DioExceptionType.connectionError:
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return true;
      case DioExceptionType.badResponse:
        return _retryStatuses.contains(err.response?.statusCode);
      case DioExceptionType.transformTimeout:
      case DioExceptionType.cancel:
      case DioExceptionType.badCertificate:
      case DioExceptionType.unknown:
        return false;
    }
  }

  Duration? _retryAfter(Response<dynamic>? response) {
    final header = response?.headers.value('retry-after');
    if (header == null) return null;
    final seconds = int.tryParse(header);
    return seconds == null ? null : Duration(seconds: seconds);
  }
}
