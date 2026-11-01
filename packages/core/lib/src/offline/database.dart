import 'dart:convert';

import 'package:drift/drift.dart';
import 'package:drift_flutter/drift_flutter.dart';

import 'tables.dart';

export 'tables.dart';

part 'database.g.dart';

/// The app's local SQLite database (Drift).
@DriftDatabase(
  tables: [SyncQueue, SyncMeta, CachedResponses, CachedEntities, LocalSessions, LocalSessionSets, PendingUploads],
)
class AuDatabase extends _$AuDatabase {
  AuDatabase(super.executor);

  /// Opens the on-device database file.
  factory AuDatabase.open({String name = 'aurum'}) => AuDatabase(driftDatabase(name: name));

  @override
  int get schemaVersion => 1;

  @override
  MigrationStrategy get migration => MigrationStrategy(
        onCreate: (m) => m.createAll(),
        beforeOpen: (details) async {
          await customStatement('PRAGMA foreign_keys = ON');
        },
      );

  // ------------------------------------------------------------- Meta

  Future<String?> readMeta(String key) async {
    final row = await (select(syncMeta)..where((t) => t.key.equals(key))).getSingleOrNull();
    return row?.value;
  }

  Future<void> writeMeta(String key, String value) =>
      into(syncMeta).insertOnConflictUpdate(SyncMetaCompanion.insert(key: key, value: value));

  // ------------------------------------------------------------- Response cache

  Future<void> cacheJson(String key, Object? json) => into(cachedResponses).insertOnConflictUpdate(
        CachedResponsesCompanion.insert(key: key, json: jsonEncode(json), updatedAt: DateTime.now().toUtc()),
      );

  Future<Object?> readCachedJson(String key) async {
    final row = await (select(cachedResponses)..where((t) => t.key.equals(key))).getSingleOrNull();
    return row == null ? null : jsonDecode(row.json);
  }

  Future<void> evictCache(String key) => (delete(cachedResponses)..where((t) => t.key.equals(key))).go();

  // ------------------------------------------------------------- Entities

  Future<void> upsertEntities(String collection, List<Map<String, dynamic>> rows) async {
    if (rows.isEmpty) return;
    final now = DateTime.now().toUtc();
    await batch((b) {
      for (final row in rows) {
        final id = row['id']?.toString();
        if (id == null) continue;
        b.insert(
          cachedEntities,
          CachedEntitiesCompanion.insert(collection: collection, id: id, json: jsonEncode(row), updatedAt: now),
          mode: InsertMode.insertOrReplace,
        );
      }
    });
  }

  Future<void> deleteEntities(String collection, List<String> ids) async {
    if (ids.isEmpty) return;
    await (delete(cachedEntities)..where((t) => t.collection.equals(collection) & t.id.isIn(ids))).go();
  }

  Future<List<Map<String, dynamic>>> entities(String collection) async {
    final rows = await (select(cachedEntities)..where((t) => t.collection.equals(collection))).get();
    return rows.map((r) => jsonDecode(r.json) as Map<String, dynamic>).toList();
  }

  /// Wipes everything (sign-out).
  Future<void> clearAll() => transaction(() async {
        for (final table in allTables) {
          await delete(table).go();
        }
      });
}
