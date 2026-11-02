import 'dart:convert';

import 'package:aurum_core/aurum_core.dart';
import 'package:drift/native.dart';
import 'package:flutter_test/flutter_test.dart';

/// An in-memory server that de-duplicates mutations by id, like the backend.
class FakeSyncServer implements SyncApi {
  final applied = <String, SyncMutation>{};
  final pushes = <List<SyncMutation>>[];
  final rejectIds = <String>{};
  final omitIds = <String>{};
  bool offline = false;

  /// Simulates a lost response: mutations are applied but the reply never
  /// reaches the device.
  bool dropResponseOnce = false;
  SyncPullResponse? nextPull;
  DateTime? lastSince;

  @override
  Future<SyncPushResponse> push(List<SyncMutation> mutations) async {
    if (offline) throw const ApiFailure(code: ApiFailure.network, message: '');
    pushes.add(mutations);
    final results = <SyncPushResultItem>[];
    for (final m in mutations) {
      if (omitIds.contains(m.id)) continue;
      if (rejectIds.contains(m.id)) {
        results.add(SyncPushResultItem(id: m.id, status: SyncResultStatus.rejected, error: 'invalid'));
      } else if (applied.containsKey(m.id)) {
        results.add(SyncPushResultItem(id: m.id, status: SyncResultStatus.duplicate));
      } else {
        applied[m.id] = m;
        results.add(SyncPushResultItem(id: m.id, status: SyncResultStatus.applied));
      }
    }
    if (dropResponseOnce) {
      dropResponseOnce = false;
      throw const ApiFailure(code: ApiFailure.timeout, message: '');
    }
    return SyncPushResponse(results: results, serverTime: DateTime.utc(2026, 11, 1, 12));
  }

  @override
  Future<SyncPullResponse> pull(DateTime? since) async {
    lastSince = since;
    return nextPull ?? SyncPullResponse(serverTime: DateTime.utc(2026, 11, 1, 12));
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  late AuDatabase db;
  late FakeSyncServer server;
  late FakeClock clock;
  late SyncEngine engine;

  setUp(() {
    db = AuDatabase(NativeDatabase.memory());
    server = FakeSyncServer();
    clock = FakeClock(DateTime.utc(2026, 11, 1, 9));
    engine = SyncEngine(db: db, api: server, clock: clock);
  });

  tearDown(() async {
    await engine.dispose();
    await db.close();
  });

  Future<String> queueFoodLog(String id) => engine.enqueue(
        entity: SyncEntity.foodLog,
        op: SyncOp.upsert,
        payload: {'id': id, 'log_date': '2026-11-01', 'slot': 'lunch', 'name': 'Machboos', 'quantity': 250},
        flushSoon: false,
      );

  test('applied mutations are pushed in creation order and removed', () async {
    await queueFoodLog('f1');
    await queueFoodLog('f2');
    expect(await engine.pendingCount(), 2);

    final report = await engine.flush();

    expect(report.applied, 2);
    expect(await engine.pendingCount(), 0);
    expect(server.pushes.single.map((m) => m.payload['id']), ['f1', 'f2']);
    expect(server.pushes.single.first.entity, 'food_log');
  });

  test('duplicate results are treated as success, rejected are kept with the error', () async {
    final dup = await queueFoodLog('f1');
    final bad = await queueFoodLog('f2');
    server.applied[dup] = SyncMutation(id: dup, entity: 'food_log', op: 'upsert', payload: const {}, clientTime: clock.now());
    server.rejectIds.add(bad);

    final report = await engine.flush();

    expect(report.duplicate, 1);
    expect(report.rejected, 1);
    expect(await engine.pendingCount(), 0);
    final rejected = await engine.rejectedEntries();
    expect(rejected.single.id, bad);
    expect(rejected.single.lastError, 'invalid');

    // The user can retry a rejected mutation after fixing it.
    server.rejectIds.clear();
    await engine.retryRejected(bad);
    await engine.flush();
    expect(await engine.rejectedEntries(), isEmpty);
    expect(server.applied.containsKey(bad), isTrue);
  });

  test('transport failures back off exponentially and never drop mutations', () async {
    await queueFoodLog('f1');
    server.offline = true;

    final first = await engine.flush();
    expect(first.failure?.code, ApiFailure.network);
    var entry = (await engine.pendingEntries()).single;
    expect(entry.attempts, 1);
    expect(entry.nextAttemptAt!.toUtc(), clock.now().add(const Duration(seconds: 2)));

    // Still inside the backoff window: nothing is sent.
    server.offline = false;
    clock.advance(const Duration(seconds: 1));
    final skipped = await engine.flush();
    expect(skipped.applied, 0);
    expect(server.pushes, isEmpty);

    // Second failure doubles the delay.
    server.offline = true;
    clock.advance(const Duration(seconds: 2));
    await engine.flush();
    entry = (await engine.pendingEntries()).single;
    expect(entry.attempts, 2);
    expect(entry.nextAttemptAt!.toUtc(), clock.now().add(const Duration(seconds: 4)));

    server.offline = false;
    clock.advance(const Duration(seconds: 4));
    final ok = await engine.flush();
    expect(ok.applied, 1);
    expect(await engine.pendingCount(), 0);
  });

  test('backoff schedule doubles and caps at maxBackoff', () {
    expect(engine.backoffFor(1), const Duration(seconds: 2));
    expect(engine.backoffFor(2), const Duration(seconds: 4));
    expect(engine.backoffFor(3), const Duration(seconds: 8));
    expect(engine.backoffFor(12), const Duration(minutes: 10));
    expect(engine.backoffFor(40), const Duration(minutes: 10));
  });

  test('idempotent replay: a lost response is replayed with the same id and applied once', () async {
    final id = await queueFoodLog('f1');
    server.dropResponseOnce = true;

    final lost = await engine.flush();
    expect(lost.failure, isNotNull);
    expect(server.applied, hasLength(1), reason: 'server applied it but the reply was lost');
    expect(await engine.pendingCount(), 1);

    clock.advance(const Duration(seconds: 3));
    final replay = await engine.flush();

    expect(replay.duplicate, 1);
    expect(server.pushes.last.single.id, id, reason: 'same mutation ULID is the idempotency key');
    expect(server.applied, hasLength(1));
    expect(await engine.pendingCount(), 0);
  });

  test('mutations the server does not report on are deferred', () async {
    final id = await queueFoodLog('f1');
    server.omitIds.add(id);
    final report = await engine.flush();
    expect(report.deferred, 1);
    expect((await engine.pendingEntries()).single.lastError, 'missing_result');
  });

  test('coalescing replaces a pending mutation for the same record', () async {
    await engine.enqueue(
      entity: SyncEntity.sessionSet,
      op: SyncOp.upsert,
      payload: {'id': 's1', 'reps': 8},
      coalesce: true,
      flushSoon: false,
    );
    await engine.enqueue(
      entity: SyncEntity.sessionSet,
      op: SyncOp.upsert,
      payload: {'id': 's1', 'reps': 10},
      coalesce: true,
      flushSoon: false,
    );
    final pending = await engine.pendingEntries();
    expect(pending, hasLength(1));
    expect(jsonDecode(pending.single.payload)['reps'], 10);
  });

  test('concurrent flush calls share one push', () async {
    await queueFoodLog('f1');
    final results = await Future.wait([engine.flush(), engine.flush(), engine.flush()]);
    expect(server.pushes, hasLength(1));
    expect(identical(results[0], results[1]), isTrue);
  });

  test('large queues are pushed in batches', () async {
    final small = SyncEngine(db: db, api: server, clock: clock, batchSize: 2);
    for (var i = 0; i < 5; i++) {
      await queueFoodLog('f$i');
    }
    final report = await small.flush();
    expect(report.applied, 5);
    expect(server.pushes.map((b) => b.length), [2, 2, 1]);
    await small.dispose();
  });

  test('pull applies changes and deletions, stores the watermark and keeps local pending rows', () async {
    await db.upsertEntities('food_logs', [
      {'id': 'gone', 'name': 'Old'},
    ]);
    await queueFoodLog('local-edit');
    server.nextPull = SyncPullResponse(
      serverTime: DateTime.utc(2026, 11, 1, 12),
      changes: {
        'food_logs': [
          {'id': 'remote', 'name': 'Harees'},
          {'id': 'local-edit', 'name': 'Server version'},
        ],
      },
      deleted: {
        'food_logs': ['gone'],
      },
    );

    await engine.pull();

    final rows = await db.entities('food_logs');
    expect(rows.map((r) => r['id']), ['remote']);
    expect(await db.readMeta(SyncEngine.watermarkKey), '2026-11-01T12:00:00.000Z');

    server.nextPull = null;
    await engine.pull();
    expect(server.lastSince, DateTime.utc(2026, 11, 1, 12));
  });

  test('sync() does nothing while offline and pushes once connectivity returns', () async {
    final connectivity = ManualConnectivitySource(online: false);
    final online = SyncEngine(db: db, api: server, clock: clock, connectivity: connectivity);
    await queueFoodLog('f1');

    await online.sync();
    expect(server.pushes, isEmpty);

    online.start();
    connectivity.online = true;
    await Future<void>.delayed(const Duration(milliseconds: 50));
    expect(server.applied, hasLength(1));
    await online.dispose();
    await connectivity.dispose();
  });

  test('status stream reports pending counts', () async {
    final statuses = <SyncStatus>[];
    final sub = engine.status.listen(statuses.add);
    await queueFoodLog('f1');
    await engine.flush();
    await Future<void>.delayed(Duration.zero);
    await sub.cancel();
    expect(statuses.any((s) => s.pending == 1), isTrue);
    expect(statuses.last.pending, 0);
    expect(statuses.last.lastSyncedAt, isNotNull);
  });
}
