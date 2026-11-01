import 'dart:async';

import 'package:dio/dio.dart';

import '../../models/identity.dart';
import '../../storage/token_store.dart';
import '../../util/clock.dart';
import '../api_failure.dart';

/// Exchanges a refresh token for a new token pair (`POST /auth/refresh`).
typedef TokenRefresher = Future<Tokens> Function(String refreshToken);

/// Request option `extra` keys understood by [AuthInterceptor].
abstract final class AuthExtra {
  /// Do not attach a bearer token (public endpoints, the refresh call itself).
  static const skipAuth = 'au.skipAuth';
  static const _retried = 'au.authRetried';
}

/// Attaches the bearer token and transparently rotates it.
///
/// * Proactive: when the stored access token is expired (with [skew]) the
///   refresh happens before the request is sent.
/// * Reactive: a 401 triggers one refresh and a single replay of the request.
/// * Queueing: concurrent requests share one in-flight refresh — the refresh
///   endpoint is called exactly once and every waiting request is replayed
///   with the new token (refresh tokens are single-use, so parallel refreshes
///   would revoke each other).
/// * A rejected refresh token clears the store and calls [onSessionExpired];
///   a network failure during refresh keeps the session (the user may simply
///   be offline).
class AuthInterceptor extends Interceptor {
  AuthInterceptor({
    required this.dio,
    required this.store,
    required this.refresher,
    this.onSessionExpired,
    this.clock = const SystemClock(),
    this.skew = const Duration(seconds: 30),
  });

  /// The client whose requests are replayed after a refresh.
  final Dio dio;
  final TokenStore store;
  final TokenRefresher refresher;
  final FutureOr<void> Function()? onSessionExpired;
  final Clock clock;
  final Duration skew;

  Future<_RefreshOutcome>? _inFlight;

  /// Number of refresh calls actually sent (useful for diagnostics/tests).
  int refreshCount = 0;

  @override
  Future<void> onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    if (options.extra[AuthExtra.skipAuth] == true) return handler.next(options);
    var tokens = await store.read();
    if (tokens != null && _isExpired(tokens)) {
      final outcome = await _refresh(tokens);
      if (outcome.tokens != null) tokens = outcome.tokens;
      if (outcome.expired) {
        return handler.reject(
          DioException(
            requestOptions: options,
            type: DioExceptionType.badResponse,
            error: const ApiFailure(code: ApiFailure.unauthenticated, message: '', statusCode: 401),
          ),
        );
      }
    }
    if (tokens != null) options.headers['Authorization'] = 'Bearer ${tokens.accessToken}';
    handler.next(options);
  }

  @override
  Future<void> onError(DioException err, ErrorInterceptorHandler handler) async {
    final options = err.requestOptions;
    if (err.response?.statusCode != 401 ||
        options.extra[AuthExtra.skipAuth] == true ||
        options.extra[AuthExtra._retried] == true) {
      return handler.next(err);
    }
    final current = await store.read();
    if (current == null) return handler.next(err);

    final sentHeader = options.headers['Authorization'];
    Tokens? fresh;
    if (sentHeader != null && sentHeader != 'Bearer ${current.accessToken}') {
      // Another request already rotated the token while this one was in flight.
      fresh = current;
    } else {
      final outcome = await _refresh(current);
      fresh = outcome.tokens;
    }
    if (fresh == null) return handler.next(err);

    options.extra[AuthExtra._retried] = true;
    options.headers['Authorization'] = 'Bearer ${fresh.accessToken}';
    try {
      final response = await dio.fetch<dynamic>(options);
      handler.resolve(response);
    } on DioException catch (e) {
      handler.next(e);
    }
  }

  bool _isExpired(Tokens tokens) {
    final expiresAt = tokens.expiresAt;
    if (expiresAt == null) return false;
    return !clock.now().toUtc().add(skew).isBefore(expiresAt.toUtc());
  }

  Future<_RefreshOutcome> _refresh(Tokens current) {
    return _inFlight ??= _doRefresh(current).whenComplete(() => _inFlight = null);
  }

  Future<_RefreshOutcome> _doRefresh(Tokens current) async {
    refreshCount++;
    try {
      final next = await refresher(current.refreshToken);
      await store.write(next);
      return _RefreshOutcome(tokens: next);
    } on DioException catch (e) {
      return _handleFailure(ApiFailure.fromDio(e));
    } on ApiFailure catch (f) {
      return _handleFailure(f);
    }
  }

  Future<_RefreshOutcome> _handleFailure(ApiFailure failure) async {
    if (failure.isTransient) return const _RefreshOutcome();
    final rejected = failure.code == ApiFailure.invalidRefreshToken ||
        failure.statusCode == 401 ||
        failure.statusCode == 400 ||
        failure.statusCode == 403 ||
        failure.statusCode == 422;
    if (rejected) {
      await store.clear();
      await onSessionExpired?.call();
      return const _RefreshOutcome(expired: true);
    }
    return const _RefreshOutcome();
  }
}

class _RefreshOutcome {
  const _RefreshOutcome({this.tokens, this.expired = false});

  final Tokens? tokens;
  final bool expired;
}
