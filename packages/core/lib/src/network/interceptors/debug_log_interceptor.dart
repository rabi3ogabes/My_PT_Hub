import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

/// Concise request logging for debug builds. Authorization headers and
/// bodies are never printed.
class DebugLogInterceptor extends Interceptor {
  DebugLogInterceptor({void Function(String)? log}) : _log = log ?? ((m) => debugPrint(m));

  final void Function(String) _log;
  static const _startKey = 'au.logStart';

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    options.extra[_startKey] = DateTime.now().millisecondsSinceEpoch;
    handler.next(options);
  }

  @override
  void onResponse(Response<dynamic> response, ResponseInterceptorHandler handler) {
    _log('[api] ${response.requestOptions.method} ${response.requestOptions.path} '
        '→ ${response.statusCode} (${_elapsed(response.requestOptions)} ms)');
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    _log('[api] ${err.requestOptions.method} ${err.requestOptions.path} '
        '✗ ${err.response?.statusCode ?? err.type.name} (${_elapsed(err.requestOptions)} ms)');
    handler.next(err);
  }

  int _elapsed(RequestOptions options) {
    final start = options.extra[_startKey] as int?;
    return start == null ? 0 : DateTime.now().millisecondsSinceEpoch - start;
  }
}
