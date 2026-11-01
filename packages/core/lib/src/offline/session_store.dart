import 'dart:convert';

import 'package:drift/drift.dart';

import '../models/sessions.dart';
import '../models/sync.dart';
import '../models/workouts.dart';
import '../network/api_client.dart';
import '../util/clock.dart';
import '../util/json_converters.dart';
import 'database.dart';
import 'sync_engine.dart';

/// Durable storage for workout sessions logged on this device.
///
/// Every change is written to SQLite immediately (inside one transaction with
/// its sync-queue mutation) so a session survives app kills, crashes and
/// reboots. Sessions are never auto-paused or discarded.
class SessionStore {
  SessionStore(this.db, this.sync, {this.clock = const SystemClock()});

  final AuDatabase db;
  final SyncEngine sync;
  final Clock clock;

  /// The most recent unfinished session, if any (restored on app start).
  Future<LocalSession?> activeSession() => (db.select(db.localSessions)
        ..where((t) => t.status.equals('in_progress'))
        ..orderBy([(t) => OrderingTerm.desc(t.startedAt)])
        ..limit(1))
      .getSingleOrNull();

  Stream<LocalSession?> watchActiveSession() => (db.select(db.localSessions)
        ..where((t) => t.status.equals('in_progress'))
        ..orderBy([(t) => OrderingTerm.desc(t.startedAt)])
        ..limit(1))
      .watchSingleOrNull();

  Future<LocalSession?> session(String id) =>
      (db.select(db.localSessions)..where((t) => t.id.equals(id))).getSingleOrNull();

  Future<List<LocalSessionSet>> sets(String sessionId) => (db.select(db.localSessionSets)
        ..where((t) => t.sessionId.equals(sessionId))
        ..orderBy([(t) => OrderingTerm.asc(t.id)]))
      .get();

  /// Locally completed sessions, newest first.
  Future<List<LocalSession>> completedSessions({int limit = 50}) => (db.select(db.localSessions)
        ..where((t) => t.status.equals('completed'))
        ..orderBy([(t) => OrderingTerm.desc(t.startedAt)])
        ..limit(limit))
      .get();

  /// Creates the session locally and queues `session` upsert.
  Future<void> start({
    required String id,
    required DateTime startedAt,
    Workout? workout,
    String? programDayId,
    String? programAssignmentId,
    String? name,
    String? stateJson,
  }) async {
    final now = clock.now().toUtc();
    await db.transaction(() async {
      await db.into(db.localSessions).insertOnConflictUpdate(
            LocalSessionsCompanion.insert(
              id: id,
              workoutId: Value(workout?.id),
              programDayId: Value(programDayId),
              programAssignmentId: Value(programAssignmentId),
              name: Value(name ?? workout?.name),
              startedAt: startedAt.toUtc(),
              workoutJson: Value(workout == null ? null : jsonEncode(workout.toJson())),
              stateJson: Value(stateJson),
              updatedAt: now,
            ),
          );
      await sync.enqueue(
        entity: SyncEntity.session,
        op: SyncOp.upsert,
        payload: compactJson({
          'id': id,
          'workout_id': workout?.id,
          'program_day_id': programDayId,
          'program_assignment_id': programAssignmentId,
          'name': name ?? workout?.name,
          'started_at': toApiDateTime(startedAt),
          'status': 'in_progress',
        }),
        coalesce: true,
      );
    });
  }

  /// Persists a set. Completed sets (and edits to them) are queued for sync;
  /// draft values on not-yet-completed sets stay local.
  Future<void> saveSet(String sessionId, SessionSet set) async {
    final now = clock.now().toUtc();
    await db.transaction(() async {
      await db.into(db.localSessionSets).insertOnConflictUpdate(
            LocalSessionSetsCompanion.insert(
              id: set.id,
              sessionId: sessionId,
              exerciseId: set.exerciseId,
              workoutExerciseId: Value(set.workoutExerciseId),
              setNumber: set.setNumber,
              setType: Value(set.setType.name),
              reps: Value(set.reps),
              weightKg: Value(set.weightKg),
              durationSeconds: Value(set.durationSeconds),
              distanceM: Value(set.distanceM),
              rpe: Value(set.rpe),
              isCompleted: Value(set.isCompleted),
              completedAt: Value(set.completedAt?.toUtc()),
              updatedAt: now,
            ),
          );
      await _touch(sessionId, now);
      final wasSynced = await _hasBeenQueued(set.id);
      if (set.isCompleted || wasSynced) {
        await sync.enqueue(
          entity: SyncEntity.sessionSet,
          op: SyncOp.upsert,
          payload: setPayload(sessionId, set),
          coalesce: true,
        );
        await db.writeMeta('set_queued:${set.id}', '1');
      }
    });
  }

  /// Removes a set the user deleted (only drafts or extra sets).
  Future<void> deleteSet(String sessionId, String setId) async {
    await db.transaction(() async {
      await (db.delete(db.localSessionSets)..where((t) => t.id.equals(setId))).go();
      if (await _hasBeenQueued(setId)) {
        await sync.enqueue(
          entity: SyncEntity.sessionSet,
          op: SyncOp.delete,
          payload: {'id': setId, 'session_id': sessionId},
        );
      }
      await _touch(sessionId, clock.now().toUtc());
    });
  }

  /// Saves the logger UI state (rest timer end, cursor, swaps, notes …).
  Future<void> saveState(String sessionId, String stateJson) =>
      (db.update(db.localSessions)..where((t) => t.id.equals(sessionId))).write(
        LocalSessionsCompanion(stateJson: Value(stateJson), updatedAt: Value(clock.now().toUtc())),
      );

  /// Marks the session completed and queues the final session upsert.
  Future<void> finish(
    String sessionId, {
    required DateTime endedAt,
    int? perceivedEffort,
    String? mood,
    String? notes,
  }) async {
    final now = clock.now().toUtc();
    await db.transaction(() async {
      await (db.update(db.localSessions)..where((t) => t.id.equals(sessionId))).write(
        LocalSessionsCompanion(
          status: const Value('completed'),
          endedAt: Value(endedAt.toUtc()),
          perceivedEffort: Value(perceivedEffort),
          mood: Value(mood),
          notes: Value(notes),
          updatedAt: Value(now),
        ),
      );
      final row = await session(sessionId);
      await sync.enqueue(
        entity: SyncEntity.session,
        op: SyncOp.upsert,
        payload: compactJson({
          'id': sessionId,
          'workout_id': row?.workoutId,
          'program_day_id': row?.programDayId,
          'program_assignment_id': row?.programAssignmentId,
          'name': row?.name,
          'started_at': row == null ? null : toApiDateTime(row.startedAt),
          'ended_at': toApiDateTime(endedAt),
          'status': 'completed',
          'perceived_effort': perceivedEffort,
          'mood': mood,
          'notes': notes,
        }),
        coalesce: true,
      );
    });
  }

  /// Abandons the session (explicit user action only).
  Future<void> abandon(String sessionId) async {
    final now = clock.now().toUtc();
    await db.transaction(() async {
      await (db.update(db.localSessions)..where((t) => t.id.equals(sessionId))).write(
        LocalSessionsCompanion(status: const Value('abandoned'), endedAt: Value(now), updatedAt: Value(now)),
      );
      await sync.enqueue(
        entity: SyncEntity.session,
        op: SyncOp.upsert,
        payload: {'id': sessionId, 'status': 'abandoned', 'ended_at': toApiDateTime(now)},
        coalesce: true,
      );
    });
  }

  /// Stores the server's completed session (PRs, volume).
  Future<void> saveResult(String sessionId, WorkoutSession result) =>
      (db.update(db.localSessions)..where((t) => t.id.equals(sessionId))).write(
        LocalSessionsCompanion(resultJson: Value(jsonEncode(result.toJson()))),
      );

  static Map<String, dynamic> setPayload(String sessionId, SessionSet set) {
    final json = set.toJson()..['session_id'] = sessionId;
    if (set.completedAt != null) json['completed_at'] = toApiDateTime(set.completedAt!);
    return compactJson(json);
  }

  static SessionSet toModel(LocalSessionSet row) => SessionSet(
        id: row.id,
        exerciseId: row.exerciseId,
        workoutExerciseId: row.workoutExerciseId,
        setNumber: row.setNumber,
        setType: SetType.values.firstWhere((t) => t.name == row.setType, orElse: () => SetType.working),
        reps: row.reps,
        weightKg: row.weightKg,
        durationSeconds: row.durationSeconds,
        distanceM: row.distanceM,
        rpe: row.rpe,
        isCompleted: row.isCompleted,
        completedAt: row.completedAt,
      );

  Future<bool> _hasBeenQueued(String setId) async => (await db.readMeta('set_queued:$setId')) != null;

  Future<void> _touch(String sessionId, DateTime now) =>
      (db.update(db.localSessions)..where((t) => t.id.equals(sessionId))).write(LocalSessionsCompanion(updatedAt: Value(now)));
}
