import 'dart:async';
import 'dart:convert';
import 'dart:math';

import 'package:drift/drift.dart';
import 'package:flutter/widgets.dart';

import '../models/sync.dart';
import '../network/api_client.dart';
import '../network/api_failure.dart';
import '../util/clock.dart';
import '../util/json_converters.dart';
import '../util/ulid.dart';
import 'connectivity.dart';
import 'database.dart';

/// Transport for the sync endpoints (`POST /sync/push`, `GET /sync/pull`).
abstract class SyncApi {
  Future<SyncPushResponse> push(List<SyncMutation> mutations);
  Future<SyncPullResponse> pull(DateTime? since);
}

class HttpSyncApi implements SyncApi {
  HttpSyncApi(this.client);

  final ApiClient client;

  @override
  Future<SyncPushResponse> push(List<SyncMutation> mutations) async {
    final envelope = await client.post(
      '/sync/push',
      data: {'mutations': mutations.map((m) => m.toJson()..['client_time'] = toApiDateTime(m.clientTime)).toList()},
      options: ApiClient.idempotent,
    );
    return SyncPushResponse.fromJson(envelope.dataMap);
  }

  @override
  Future<SyncPullResponse> pull(DateTime? since) async {
    final envelope = await client.get('/sync/pull', query: {'since': since == null ? null : toApiDateTime(since)});
    return SyncPullResponse.fromJson(envelope.dataMap);
  }
}

/// Snapshot of the engine for UI (badges, "syncing…" indicators).
@immutable
class SyncStatus {
  const SyncStatus({this.pending = 0, this.rejected = 0, this.syncing = false, this.lastSyncedAt, this.lastError});

  final int pending;
  final int rejected;
  final bool syncing;
  final DateTime? lastSyncedAt;
  final String? lastError;

  SyncStatus copyWith({int? pending, int? rejected, bool? syncing, DateTime? lastSyncedAt, String? lastError}) =>
      SyncStatus(
        pending: pending ?? this.pending,
        rejected: rejected ?? this.rejected,
        syncing: syncing ?? this.syncing,
        lastSyncedAt: lastSyncedAt ?? this.lastSyncedAt,
        lastError: lastError,
      );
}

/// Outcome of one [SyncEngine.flush].
@immutable
class FlushReport {
  const FlushReport({this.applied = 0, this.duplicate = 0, this.rejected = 0, this.deferred = 0, this.failure});

  final int applied;
  final int duplicate;
  final int rejected;
  final int deferred;
  final ApiFailure? failure;

  FlushReport operator +(FlushReport o) => FlushReport(
        applied: applied + o.applied,
        duplicate: duplicate + o.duplicate,
        rejected: rejected + o.rejected,
        deferred: deferred + o.deferred,
        failure: o.failure ?? failure,
      );

  @override
  String toString() => 'FlushReport(applied: $applied, duplicate: $duplicate, rejected: $rejected, deferred: $deferred)';
}

/// Applies server rows pulled for a collection into domain tables. Return
/// true when handled (the generic entity cache is always updated too).
typedef PullApplier = Future<void> Function(AuDatabase db, List<Map<String, dynamic>> rows, List<String> deletedIds);

/// Offline-first sync engine.
///
/// * [enqueue] writes the mutation to `sync_queue` (same transaction as the
///   local write when called inside one) and schedules a flush.
/// * [flush] replays pending mutations in ULID order in batches. Results map
///   to: `applied`/`duplicate` → removed; `rejected` → kept with the error for
///   the user to see; transport failure → exponential backoff
///   (`base * 2^(attempts-1)`, capped). Mutations are never dropped because of
///   transient errors — replays are safe because the server de-duplicates by
///   mutation id (idempotent replay).
/// * [pull] fetches changes since the stored watermark and applies them,
///   skipping rows that still have local pending mutations (local wins until
///   pushed; server timestamps decide afterwards).
/// * [start] wires triggers: connectivity regained, app resume and a periodic
///   timer.
class SyncEngine {
  SyncEngine({
    required this.db,
    required this.api,
    ConnectivitySource? connectivity,
    this.clock = const SystemClock(),
    this.batchSize = 100,
    this.baseBackoff = const Duration(seconds: 2),
    this.maxBackoff = const Duration(minutes: 10),
    this.periodicInterval = const Duration(minutes: 5),
    Map<String, PullApplier>? appliers,
  })  : connectivity = connectivity ?? ManualConnectivitySource(),
        appliers = appliers ?? const {};

  static const watermarkKey = 'sync.pull_since';
  static const lastSyncKey = 'sync.last_synced_at';

  final AuDatabase db;
  final SyncApi api;
  final ConnectivitySource connectivity;
  final Clock clock;
  final int batchSize;
  final Duration baseBackoff;
  final Duration maxBackoff;
  final Duration periodicInterval;
  final Map<String, PullApplier> appliers;

  final _status = StreamController<SyncStatus>.broadcast();
  SyncStatus _current = const SyncStatus();
  Future<FlushReport>? _flushing;
  Future<void>? _pulling;
  StreamSubscription<bool>? _connectivitySub;
  Timer? _timer;
  AppLifecycleListener? _lifecycle;
  Timer? _debounce;

  /// Status updates for UI.
  Stream<SyncStatus> get status => _status.stream;
  SyncStatus get currentStatus => _current;

  /// Backoff delay after [attempts] failed attempts (attempts ≥ 1).
  Duration backoffFor(int attempts) {
    final factor = pow(2, max(0, attempts - 1)).toInt();
    final ms = min(baseBackoff.inMilliseconds * factor, maxBackoff.inMilliseconds);
    return Duration(milliseconds: ms);
  }

  /// Queues a mutation and returns its ULID. The flush runs shortly after so
  /// bursts (e.g. completing several sets) are batched into one request.
  Future<String> enqueue({
    required String entity,
    required String op,
    required Map<String, dynamic> payload,
    String? id,
    bool flushSoon = true,
    bool coalesce = false,
  }) async {
    final mutationId = id ?? Ulid.generate();
    final now = clock.now().toUtc();
    final entityId = payload['id'];
    if (coalesce && entityId != null) {
      // Replace a still-pending mutation of the same record: the newest
      // payload carries every field, so only one request is needed.
      await db.customStatement(
        "DELETE FROM sync_queue WHERE entity = ? AND op = ? AND status = 'pending' "
        "AND json_extract(payload, '\$.id') = ?",
        [entity, op, entityId.toString()],
      );
    }
    await db.into(db.syncQueue).insert(
          SyncQueueCompanion.insert(
            id: mutationId,
            entity: entity,
            op: op,
            payload: jsonEncode(payload),
            createdAt: now,
          ),
          mode: InsertMode.insertOrReplace,
        );
    await _refreshCounts();
    if (flushSoon) scheduleFlush();
    return mutationId;
  }

  /// Debounced flush trigger.
  void scheduleFlush([Duration delay = const Duration(milliseconds: 600)]) {
    _debounce?.cancel();
    _debounce = Timer(delay, () => unawaited(_safeSync(pullAfter: false)));
  }

  /// Number of mutations waiting to be pushed.
  Future<int> pendingCount() async {
    final count = countAll();
    final query = db.selectOnly(db.syncQueue)
      ..addColumns([count])
      ..where(db.syncQueue.status.equals('pending'));
    return (await query.getSingle()).read(count) ?? 0;
  }

  /// Pending mutations (oldest first).
  Future<List<SyncQueueEntry>> pendingEntries() =>
      (db.select(db.syncQueue)..where((t) => t.status.equals('pending'))..orderBy([(t) => OrderingTerm.asc(t.id)])).get();

  /// Mutations the server refused (shown to the user with their error).
  Future<List<SyncQueueEntry>> rejectedEntries() =>
      (db.select(db.syncQueue)..where((t) => t.status.equals('rejected'))).get();

  /// Re-queues a rejected mutation (e.g. after the user fixed the data).
  Future<void> retryRejected(String id) async {
    await (db.update(db.syncQueue)..where((t) => t.id.equals(id))).write(
      const SyncQueueCompanion(status: Value('pending'), attempts: Value(0), nextAttemptAt: Value(null)),
    );
    await _refreshCounts();
    scheduleFlush(Duration.zero);
  }

  Future<void> discardRejected(String id) async {
    await (db.delete(db.syncQueue)..where((t) => t.id.equals(id))).go();
    await _refreshCounts();
  }

  /// Pushes due mutations. Concurrent callers share the same run.
  Future<FlushReport> flush({bool ignoreBackoff = false}) {
    return _flushing ??= _flush(ignoreBackoff: ignoreBackoff).whenComplete(() => _flushing = null);
  }

  Future<FlushReport> _flush({required bool ignoreBackoff}) async {
    var report = const FlushReport();
    _emit(_current.copyWith(syncing: true));
    try {
      final attempted = <String>{};
      while (true) {
        final now = clock.now().toUtc();
        final query = db.select(db.syncQueue)
          ..where((t) => t.status.equals('pending'))
          ..orderBy([(t) => OrderingTerm.asc(t.id)])
          ..limit(batchSize);
        if (!ignoreBackoff) {
          query.where((t) => t.nextAttemptAt.isNull() | t.nextAttemptAt.isSmallerOrEqualValue(now));
        }
        final rows = (await query.get()).where((r) => !attempted.contains(r.id)).toList();
        if (rows.isEmpty) break;
        attempted.addAll(rows.map((r) => r.id));

        final batch = await _pushBatch(rows);
        report = report + batch;
        if (batch.failure != null) break;
      }
      if (report.failure == null) {
        await db.writeMeta(lastSyncKey, clock.now().toUtc().toIso8601String());
        _emit(_current.copyWith(lastSyncedAt: clock.now().toUtc()));
      } else {
        _emit(_current.copyWith(lastError: report.failure!.code));
      }
      return report;
    } finally {
      await _refreshCounts(syncing: false);
    }
  }

  Future<FlushReport> _pushBatch(List<SyncQueueEntry> rows) async {
    final mutations = rows
        .map((r) => SyncMutation(
              id: r.id,
              entity: r.entity,
              op: r.op,
              payload: jsonDecode(r.payload) as Map<String, dynamic>,
              clientTime: r.createdAt,
            ))
        .toList();
    try {
      final response = await api.push(mutations);
      final byId = {for (final r in response.results) r.id: r};
      var applied = 0, duplicate = 0, rejected = 0, deferred = 0;
      await db.transaction(() async {
        for (final row in rows) {
          final result = byId[row.id];
          switch (result?.status) {
            case SyncResultStatus.applied:
              applied++;
              await (db.delete(db.syncQueue)..where((t) => t.id.equals(row.id))).go();
            case SyncResultStatus.duplicate:
              duplicate++;
              await (db.delete(db.syncQueue)..where((t) => t.id.equals(row.id))).go();
            case SyncResultStatus.rejected:
              rejected++;
              await (db.update(db.syncQueue)..where((t) => t.id.equals(row.id))).write(
                SyncQueueCompanion(
                  status: const Value('rejected'),
                  attempts: Value(row.attempts + 1),
                  lastError: Value(result?.error ?? 'rejected'),
                ),
              );
            case null:
              // The server did not report on this mutation: retry later.
              deferred++;
              await _defer(row, 'missing_result');
          }
        }
      });
      return FlushReport(applied: applied, duplicate: duplicate, rejected: rejected, deferred: deferred);
    } on ApiFailure catch (failure) {
      await db.transaction(() async {
        for (final row in rows) {
          await _defer(row, failure.code);
        }
      });
      return FlushReport(deferred: rows.length, failure: failure);
    }
  }

  Future<void> _defer(SyncQueueEntry row, String error) async {
    final attempts = row.attempts + 1;
    await (db.update(db.syncQueue)..where((t) => t.id.equals(row.id))).write(
      SyncQueueCompanion(
        attempts: Value(attempts),
        lastError: Value(error),
        nextAttemptAt: Value(clock.now().toUtc().add(backoffFor(attempts))),
      ),
    );
  }

  /// Pulls server changes since the watermark.
  Future<void> pull() => _pulling ??= _pull().whenComplete(() => _pulling = null);

  Future<void> _pull() async {
    final sinceRaw = await db.readMeta(watermarkKey);
    final since = sinceRaw == null ? null : DateTime.tryParse(sinceRaw);
    final response = await api.pull(since);

    final pendingIds = (await pendingEntries())
        .map((e) => (jsonDecode(e.payload) as Map<String, dynamic>)['id']?.toString())
        .whereType<String>()
        .toSet();

    await db.transaction(() async {
      final collections = {...response.changes.keys, ...response.deleted.keys};
      for (final collection in collections) {
        final rows = (response.changes[collection] ?? const [])
            .whereType<Map<dynamic, dynamic>>()
            .map((m) => m.cast<String, dynamic>())
            .where((m) => !pendingIds.contains(m['id']?.toString()))
            .toList();
        final deleted = (response.deleted[collection] ?? const []).map((e) => e.toString()).toList();
        await db.upsertEntities(collection, rows);
        await db.deleteEntities(collection, deleted);
        final applier = appliers[collection];
        if (applier != null) await applier(db, rows, deleted);
      }
      await db.writeMeta(watermarkKey, response.serverTime.toUtc().toIso8601String());
    });
  }

  /// Full cycle: push then pull. Errors are captured in [status].
  Future<void> sync() => _safeSync(pullAfter: true);

  Future<void> _safeSync({required bool pullAfter}) async {
    if (!await connectivity.isOnline()) return;
    try {
      final report = await flush();
      if (pullAfter && report.failure == null) await pull();
    } on ApiFailure catch (f) {
      _emit(_current.copyWith(lastError: f.code));
    }
  }

  /// Starts automatic triggers.
  void start() {
    _connectivitySub ??= connectivity.onlineChanges.listen((online) {
      if (online) unawaited(sync());
    });
    _timer ??= Timer.periodic(periodicInterval, (_) => unawaited(sync()));
    _lifecycle ??= AppLifecycleListener(onResume: () => unawaited(sync()));
    unawaited(_refreshCounts());
    unawaited(sync());
  }

  Future<void> stop() async {
    await _connectivitySub?.cancel();
    _connectivitySub = null;
    _timer?.cancel();
    _timer = null;
    _debounce?.cancel();
    _lifecycle?.dispose();
    _lifecycle = null;
  }

  Future<void> dispose() async {
    await stop();
    await _status.close();
  }

  Future<void> _refreshCounts({bool? syncing}) async {
    final pending = await pendingCount();
    final rejected = (await rejectedEntries()).length;
    _emit(_current.copyWith(pending: pending, rejected: rejected, syncing: syncing, lastError: _current.lastError));
  }

  void _emit(SyncStatus status) {
    _current = status;
    if (!_status.isClosed) _status.add(status);
  }
}
