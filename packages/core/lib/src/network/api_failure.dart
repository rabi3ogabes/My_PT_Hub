import 'dart:io';

import 'package:dio/dio.dart';

/// Typed failure for every API call. UI code matches on [code] (contract
/// codes such as `validation_failed`, plus client codes `network`,
/// `timeout`, `server_error`, `cancelled`, `unknown`).
class ApiFailure implements Exception {
  const ApiFailure({
    required this.code,
    required this.message,
    this.statusCode,
    this.fieldErrors = const {},
  });

  /// Parses the first error of an envelope `{errors: [{code, message, field_errors}]}`.
  factory ApiFailure.fromEnvelope(Object? body, {int? statusCode}) {
    if (body is Map && body['errors'] is List && (body['errors'] as List).isNotEmpty) {
      final first = (body['errors'] as List).first;
      if (first is Map) {
        final rawFields = first['field_errors'];
        final fields = <String, List<String>>{};
        if (rawFields is Map) {
          rawFields.forEach((key, value) {
            fields[key.toString()] = value is List ? value.map((e) => e.toString()).toList() : [value.toString()];
          });
        }
        return ApiFailure(
          code: (first['code'] ?? _codeForStatus(statusCode)).toString(),
          message: (first['message'] ?? '').toString(),
          statusCode: statusCode,
          fieldErrors: fields,
        );
      }
    }
    return ApiFailure(code: _codeForStatus(statusCode), message: '', statusCode: statusCode);
  }

  /// Maps any [DioException] to an [ApiFailure].
  factory ApiFailure.fromDio(DioException e) {
    if (e.error is ApiFailure) return e.error! as ApiFailure;
    switch (e.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
      case DioExceptionType.transformTimeout:
        return const ApiFailure(code: timeout, message: '');
      case DioExceptionType.connectionError:
        return const ApiFailure(code: network, message: '');
      case DioExceptionType.cancel:
        return const ApiFailure(code: cancelled, message: '');
      case DioExceptionType.badCertificate:
        return const ApiFailure(code: network, message: '');
      case DioExceptionType.badResponse:
        return ApiFailure.fromEnvelope(e.response?.data, statusCode: e.response?.statusCode);
      case DioExceptionType.unknown:
        if (e.error is SocketException || e.error is HttpException) {
          return const ApiFailure(code: network, message: '');
        }
        return ApiFailure(code: unknown, message: e.message ?? '');
    }
  }

  // Contract codes.
  static const unauthenticated = 'unauthenticated';
  static const invalidRefreshToken = 'invalid_refresh_token';
  static const forbidden = 'forbidden';
  static const notFound = 'not_found';
  static const validationFailed = 'validation_failed';
  static const planLimitReached = 'plan_limit_reached';
  static const accountLocked = 'account_locked';
  static const tooManyRequests = 'too_many_requests';
  static const invitationInvalid = 'invitation_invalid';
  // Client-side codes.
  static const network = 'network';
  static const timeout = 'timeout';
  static const serverError = 'server_error';
  static const cancelled = 'cancelled';
  static const unknown = 'unknown';

  final String code;
  final String message;
  final int? statusCode;
  final Map<String, List<String>> fieldErrors;

  /// True when the request never reached the server (offline, DNS, timeout).
  bool get isConnectivity => code == network || code == timeout;

  /// True for failures worth retrying later (sync queue keeps the mutation).
  bool get isTransient =>
      isConnectivity || code == serverError || code == tooManyRequests || (statusCode != null && statusCode! >= 500);

  bool get isUnauthenticated => code == unauthenticated || code == invalidRefreshToken || statusCode == 401;

  /// First error message for [field], if any.
  String? fieldError(String field) => fieldErrors[field]?.firstOrNull;

  static String _codeForStatus(int? status) {
    switch (status) {
      case 401:
        return unauthenticated;
      case 402:
        return planLimitReached;
      case 403:
        return forbidden;
      case 404:
        return notFound;
      case 410:
        return invitationInvalid;
      case 422:
        return validationFailed;
      case 423:
        return accountLocked;
      case 429:
        return tooManyRequests;
    }
    if (status != null && status >= 500) return serverError;
    return unknown;
  }

  @override
  String toString() => 'ApiFailure($code, $statusCode, $message)';
}
