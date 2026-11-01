import 'package:dio/dio.dart';

/// Adds `Accept`, `Accept-Language`, `X-Tenant` and client identification
/// headers to every request. Values are read lazily so locale or tenant
/// switches apply to the very next call.
class HeadersInterceptor extends Interceptor {
  HeadersInterceptor({
    required this.locale,
    required this.tenantId,
    this.appName,
    this.appVersion,
  });

  final String Function() locale;
  final String? Function() tenantId;
  final String? appName;
  final String? appVersion;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    options.headers['Accept'] = 'application/json';
    options.headers['Accept-Language'] = locale();
    final tenant = tenantId();
    if (tenant != null && tenant.isNotEmpty && !options.headers.containsKey('X-Tenant')) {
      options.headers['X-Tenant'] = tenant;
    }
    if (appName != null) options.headers['X-Client-App'] = appName;
    if (appVersion != null) options.headers['X-Client-Version'] = appVersion;
    handler.next(options);
  }
}
