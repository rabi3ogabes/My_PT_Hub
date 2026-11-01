import 'package:dio/dio.dart';

import '../api_failure.dart';
import '../envelope.dart';

/// Unwraps `{data, meta, links, errors}` into an [Envelope] on success and
/// converts error responses into a [DioException] carrying a typed
/// [ApiFailure] (`error` field), so callers never parse raw bodies.
class EnvelopeInterceptor extends Interceptor {
  @override
  void onResponse(Response<dynamic> response, ResponseInterceptorHandler handler) {
    if (response.data is! Envelope && response.requestOptions.responseType == ResponseType.json) {
      response.data = Envelope.parse(response.data);
    }
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    if (err.error is ApiFailure) return handler.next(err);
    handler.next(err.copyWith(error: ApiFailure.fromDio(err)));
  }
}
