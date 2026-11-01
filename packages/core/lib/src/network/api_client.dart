import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../models/common.dart';
import '../models/identity.dart';
import '../storage/token_store.dart';
import '../util/clock.dart';
import 'api_failure.dart';
import 'envelope.dart';
import 'interceptors/auth_interceptor.dart';
import 'interceptors/debug_log_interceptor.dart';
import 'interceptors/envelope_interceptor.dart';
import 'interceptors/headers_interceptor.dart';
import 'interceptors/retry_interceptor.dart';

/// Static configuration of the API client for one app build.
class ApiConfig {
  const ApiConfig({
    required this.baseUrl,
    required this.app,
    this.appVersion,
    this.connectTimeout = const Duration(seconds: 15),
    this.receiveTimeout = const Duration(seconds: 30),
    this.enableLogging = kDebugMode,
  });

  /// Including `/api/v1`, e.g. `https://api.aurum.coach/api/v1`.
  final String baseUrl;

  /// `client` or `coach` (sent as `app` on device registration).
  final String app;
  final String? appVersion;
  final Duration connectTimeout;
  final Duration receiveTimeout;
  final bool enableLogging;
}

/// Thin typed facade over Dio. Every method returns an unwrapped [Envelope]
/// (or a decoded model) and throws [ApiFailure] — never a raw DioException.
class ApiClient {
  ApiClient({
    required this.config,
    required this.tokenStore,
    required String Function() locale,
    required String? Function() tenantId,
    FutureOr<void> Function()? onSessionExpired,
    Clock clock = const SystemClock(),
    HttpClientAdapter? adapter,
    Future<void> Function(Duration)? retrySleep,
  }) : dio = Dio(_options(config)) {
    if (adapter != null) dio.httpClientAdapter = adapter;

    // A bare client for the refresh call: no auth interceptor (avoids
    // recursion) and no retry (a refresh must not be replayed blindly).
    final refreshDio = Dio(_options(config));
    if (adapter != null) refreshDio.httpClientAdapter = adapter;
    refreshDio.interceptors.addAll([
      HeadersInterceptor(locale: locale, tenantId: () => null, appName: config.app, appVersion: config.appVersion),
      EnvelopeInterceptor(),
    ]);

    auth = AuthInterceptor(
      dio: dio,
      store: tokenStore,
      clock: clock,
      onSessionExpired: onSessionExpired,
      refresher: (refreshToken) async {
        final response = await refreshDio.post<dynamic>('/auth/refresh', data: {'refresh_token': refreshToken});
        return Tokens.fromJson((response.data as Envelope).dataMap);
      },
    );

    dio.interceptors.addAll([
      HeadersInterceptor(locale: locale, tenantId: tenantId, appName: config.app, appVersion: config.appVersion),
      auth,
      RetryInterceptor(dio: dio, sleep: retrySleep),
      EnvelopeInterceptor(),
      if (config.enableLogging) DebugLogInterceptor(),
    ]);
  }

  final ApiConfig config;
  final TokenStore tokenStore;
  final Dio dio;
  late final AuthInterceptor auth;

  static BaseOptions _options(ApiConfig config) => BaseOptions(
        baseUrl: config.baseUrl,
        connectTimeout: config.connectTimeout,
        receiveTimeout: config.receiveTimeout,
        contentType: Headers.jsonContentType,
        responseType: ResponseType.json,
      );

  Future<Envelope> get(String path, {Map<String, dynamic>? query, Options? options, CancelToken? cancelToken}) =>
      _run(() => dio.get<dynamic>(path, queryParameters: _clean(query), options: options, cancelToken: cancelToken));

  Future<Envelope> post(String path, {Object? data, Map<String, dynamic>? query, Options? options}) =>
      _run(() => dio.post<dynamic>(path, data: data, queryParameters: _clean(query), options: options));

  Future<Envelope> put(String path, {Object? data, Options? options}) =>
      _run(() => dio.put<dynamic>(path, data: data, options: options));

  Future<Envelope> patch(String path, {Object? data, Options? options}) =>
      _run(() => dio.patch<dynamic>(path, data: data, options: options));

  Future<Envelope> delete(String path, {Object? data, Options? options}) =>
      _run(() => dio.delete<dynamic>(path, data: data, options: options));

  /// Multipart upload with progress (0..1).
  Future<Envelope> upload(String path, FormData form, {void Function(double progress)? onProgress}) => _run(
        () => dio.post<dynamic>(
          path,
          data: form,
          onSendProgress: onProgress == null ? null : (sent, total) => onProgress(total <= 0 ? 0 : sent / total),
        ),
      );

  /// GET and decode `data` as a single object (null when `data: null`).
  Future<T?> getObject<T>(String path, T Function(Map<String, dynamic>) decode, {Map<String, dynamic>? query}) async {
    final envelope = await get(path, query: query);
    final data = envelope.data;
    return data is Map ? decode(data.cast<String, dynamic>()) : null;
  }

  /// GET and decode `data` as a list.
  Future<List<T>> getList<T>(String path, T Function(Map<String, dynamic>) decode, {Map<String, dynamic>? query}) async {
    final envelope = await get(path, query: query);
    return envelope.dataList.map(decode).toList();
  }

  /// GET a cursor-paginated list.
  Future<CursorPage<T>> getPage<T>(
    String path,
    T Function(Map<String, dynamic>) decode, {
    String? cursor,
    Map<String, dynamic>? query,
  }) async {
    final envelope = await get(path, query: {...?query, 'cursor': ?cursor});
    return CursorPage.fromEnvelope(envelope.data, envelope.meta, decode);
  }

  /// Options that skip the bearer token (public endpoints).
  static Options get public => Options(extra: {AuthExtra.skipAuth: true});

  /// Options that allow retrying a POST the server de-duplicates by id.
  static Options get idempotent => Options(extra: {RetryExtra.retryable: true});

  Future<Envelope> _run(Future<Response<dynamic>> Function() call) async {
    try {
      final response = await call();
      final data = response.data;
      return data is Envelope ? data : Envelope.parse(data);
    } on DioException catch (e) {
      throw ApiFailure.fromDio(e);
    }
  }

  static Map<String, dynamic>? _clean(Map<String, dynamic>? query) {
    if (query == null) return null;
    return Map.of(query)..removeWhere((_, v) => v == null || (v is String && v.isEmpty));
  }
}

/// Removes null values from a request body map.
Map<String, dynamic> compactJson(Map<String, dynamic> map) => Map.of(map)..removeWhere((_, v) => v == null);
