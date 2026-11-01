import 'dart:convert';

import 'package:aurum_core/aurum_core.dart';
import 'package:drift/native.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  late AuDatabase db;
  late SyncEngine engine;
  late SessionStore store;

  setUp(() {
    db = AuDatabase(NativeDatabase.memory());
    engine = SyncEngine(db: db, api: _NoApi(), clock: FakeClock(DateTime.utc(2026, 11, 1, 9)));
    store = SessionStore(db, engine);
  });

  tearDown(() async {
    await engine.dispose();
    await db.close();
  });

  SessionSet set(String id, {bool done = false, int reps = 8}) => SessionSet(
        id: id,
        exerciseId: 'ex1',
        workoutExerciseId: 'we1',
        setNumber: 1,
        reps: reps,
        weightKg: 80,
        isCompleted: done,
        completedAt: done ? DateTime.utc(2026, 11, 1, 9, 5) : null,
      );

  test('start creates a session and queues the session upsert', () async {
    await store.start(id: 'S1', startedAt: DateTime.utc(2026, 11, 1, 9), name: 'Push A');
    final active = await store.activeSession();
    expect(active!.id, 'S1');
    final pending = await engine.pendingEntries();
    expect(pending.single.entity, 'session');
    expect(jsonDecode(pending.single.payload)['started_at'], '2026-11-01T09:00:00.000Z');
  });

  test('draft sets stay local; completed sets are queued with session_id', () async {
    await store.start(id: 'S1', startedAt: DateTime.utc(2026, 11, 1, 9));
    await store.saveSet('S1', set('set1'));
    expect((await engine.pendingEntries()).where((e) => e.entity == 'session_set'), isEmpty);

    await store.saveSet('S1', set('set1', done: true));
    var setMutations = (await engine.pendingEntries()).where((e) => e.entity == 'session_set').toList();
    expect(setMutations, hasLength(1));
    expect(jsonDecode(setMutations.single.payload)['session_id'], 'S1');

    // Editing a completed set coalesces into one pending mutation.
    await store.saveSet('S1', set('set1', done: true, reps: 10));
    setMutations = (await engine.pendingEntries()).where((e) => e.entity == 'session_set').toList();
    expect(setMutations, hasLength(1));
    expect(jsonDecode(setMutations.single.payload)['reps'], 10);

    final rows = await store.sets('S1');
    expect(rows.single.reps, 10);
    expect(SessionStore.toModel(rows.single).isCompleted, isTrue);
  });

  test('finish marks the session completed and is no longer active', () async {
    await store.start(id: 'S1', startedAt: DateTime.utc(2026, 11, 1, 9));
    await store.finish('S1', endedAt: DateTime.utc(2026, 11, 1, 10), perceivedEffort: 8, notes: 'Strong');
    expect(await store.activeSession(), isNull);
    final done = await store.completedSessions();
    expect(done.single.perceivedEffort, 8);
    final sessionMutations = (await engine.pendingEntries()).where((e) => e.entity == 'session').toList();
    expect(sessionMutations, hasLength(1), reason: 'start + finish coalesce');
    expect(jsonDecode(sessionMutations.single.payload)['status'], 'completed');
  });
}

class _NoApi implements SyncApi {
  @override
  Future<SyncPullResponse> pull(DateTime? since) async => SyncPullResponse(serverTime: DateTime.utc(2026));

  @override
  Future<SyncPushResponse> push(List<SyncMutation> mutations) async => const SyncPushResponse();
}
