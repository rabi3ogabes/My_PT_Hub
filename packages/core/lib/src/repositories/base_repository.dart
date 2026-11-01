import '../network/api_client.dart';
import '../network/api_failure.dart';
import '../offline/database.dart';

/// A value plus whether it came from the offline cache.
class Fetched<T> {
  const Fetched(this.value, {this.fromCache = false, this.cachedAt});

  final T value;
  final bool fromCache;
  final DateTime? cachedAt;
}

/// Shared helpers: network-first reads that fall back to the Drift cache
/// when the device is offline.
abstract class BaseRepository {
  BaseRepository(this.api, this.db);

  final ApiClient api;
  final AuDatabase db;

  /// Fetches `data` from [path], caches it under [cacheKey] and decodes it.
  /// On a connectivity failure the cached copy is returned instead.
  Future<Fetched<T>> cachedGet<T>(
    String cacheKey,
    String path,
    T Function(Object? data) decode, {
    Map<String, dynamic>? query,
  }) async {
    try {
      final envelope = await api.get(path, query: query);
      await db.cacheJson(cacheKey, envelope.data);
      return Fetched(decode(envelope.data));
    } on ApiFailure catch (failure) {
      if (!failure.isTransient) rethrow;
      final cached = await db.readCachedJson(cacheKey);
      if (cached == null && !(await _hasKey(cacheKey))) rethrow;
      return Fetched(decode(cached), fromCache: true);
    }
  }

  Future<bool> _hasKey(String key) async {
    final row = await (db.select(db.cachedResponses)..where((t) => t.key.equals(key))).getSingleOrNull();
    return row != null;
  }

  static Map<String, dynamic> asMap(Object? data) => (data as Map).cast<String, dynamic>();

  static List<Map<String, dynamic>> asList(Object? data) =>
      (data as List? ?? const []).map((e) => (e as Map).cast<String, dynamic>()).toList();
}
