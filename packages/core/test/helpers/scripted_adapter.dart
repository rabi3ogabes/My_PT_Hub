import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';

/// A response produced by a [ScriptedAdapter] handler.
class Reply {
  const Reply(this.status, [this.body, this.headers = const {}]);

  /// Envelope success `{data, meta, links, errors: []}`.
  factory Reply.ok(Object? data, {Map<String, dynamic> meta = const {}}) =>
      Reply(200, {'data': data, 'meta': meta, 'links': <String, dynamic>{}, 'errors': <dynamic>[]});

  /// Envelope error.
  factory Reply.error(int status, String code, {String message = '', Map<String, dynamic>? fieldErrors}) => Reply(status, {
        'data': null,
        'meta': <String, dynamic>{},
        'links': <String, dynamic>{},
        'errors': [
          {'code': code, 'message': message, 'field_errors': ?fieldErrors},
        ],
      });

  final int status;
  final Object? body;
  final Map<String, List<String>> headers;
}

/// Signals a transport failure (no response).
class NetworkDown implements Exception {
  const NetworkDown();
}

typedef ReplyHandler = Future<Reply> Function(RequestOptions options);

/// A Dio adapter driven by a handler; records every request it sees.
class ScriptedAdapter implements HttpClientAdapter {
  ScriptedAdapter(this.handler);

  ReplyHandler handler;
  final requests = <RequestOptions>[];

  List<RequestOptions> requestsTo(String path) => requests.where((r) => r.path == path).toList();

  @override
  Future<ResponseBody> fetch(RequestOptions options, Stream<Uint8List>? requestStream, Future<void>? cancelFuture) async {
    requests.add(options);
    final Reply reply;
    try {
      reply = await handler(options);
    } on NetworkDown {
      throw DioException.connectionError(requestOptions: options, reason: 'offline');
    }
    return ResponseBody.fromString(
      reply.body == null ? '' : jsonEncode(reply.body),
      reply.status,
      headers: {
        Headers.contentTypeHeader: [Headers.jsonContentType],
        ...reply.headers,
      },
    );
  }

  @override
  void close({bool force = false}) {}
}

/// Decodes the JSON body Dio sent.
Map<String, dynamic> bodyOf(RequestOptions options) {
  final data = options.data;
  if (data is Map<String, dynamic>) return data;
  if (data is String) return jsonDecode(data) as Map<String, dynamic>;
  return <String, dynamic>{};
}
