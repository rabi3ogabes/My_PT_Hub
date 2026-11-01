/// The unwrapped `{data, meta, links}` envelope.
class Envelope {
  const Envelope({this.data, this.meta = const {}, this.links = const {}});

  /// Unwraps a decoded response body. Non-envelope bodies are passed through
  /// as `data` so non-conforming endpoints (e.g. file downloads) still work.
  factory Envelope.parse(Object? body) {
    if (isEnvelope(body)) {
      final map = body! as Map;
      return Envelope(
        data: map['data'],
        meta: _asMap(map['meta']),
        links: _asMap(map['links']),
      );
    }
    return Envelope(data: body);
  }

  final Object? data;
  final Map<String, dynamic> meta;
  final Map<String, dynamic> links;

  static bool isEnvelope(Object? body) => body is Map && body.containsKey('data') && (body.containsKey('meta') || body.containsKey('errors') || body.containsKey('links'));

  Map<String, dynamic> get dataMap => (data as Map).cast<String, dynamic>();

  List<Map<String, dynamic>> get dataList => (data as List? ?? const []).map((e) => (e as Map).cast<String, dynamic>()).toList();

  static Map<String, dynamic> _asMap(Object? value) => value is Map ? value.cast<String, dynamic>() : <String, dynamic>{};
}
