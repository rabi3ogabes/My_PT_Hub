import 'dart:async';
import 'dart:convert';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'audio_cues.dart';
import 'logger_state.dart';

/// Result of finishing a session.
class FinishResult {
  const FinishResult({required this.summary, this.server});

  final SessionSummary summary;

  /// Server-confirmed session with detected PRs (null when offline — the
  /// session is queued and PRs appear once it syncs).
  final WorkoutSession? server;

  List<PersonalRecord> get records => server?.personalRecords ?? const [];
}

/// Local summary for the finish screen.
class SessionSummary {
  const SessionSummary({
    required this.name,
    required this.duration,
    required this.volumeKg,
    required this.completedSets,
    required this.totalSets,
    required this.exercises,
  });

  factory SessionSummary.of(LoggerState s, DateTime now) => SessionSummary(
        name: s.name,
        duration: s.elapsed(now),
        volumeKg: s.totalVolumeKg,
        completedSets: s.completedSets,
        totalSets: s.totalSets,
        exercises: s.allExercises.where((e) => e.sets.any((x) => x.completed)).length,
      );

  final String? name;
  final Duration duration;
  final double volumeKg;
  final int completedSets;
  final int totalSets;
  final int exercises;
}

/// Notification id used for the "rest is over" alarm.
const kRestNotificationId = 7001;

/// Drives the active workout. Every mutation is persisted to SQLite
/// immediately (state snapshot + changed sets, completed sets queued for
/// sync) before the UI moves on, so killing the app never loses data.
class LoggerController extends Notifier<LoggerState?> {
  Future<void> _writes = Future.value();

  @override
  LoggerState? build() => null;

  SessionStore get _store => ref.read(sessionStoreProvider);
  Clock get _clock => ref.read(clockProvider);
  DateTime get _now => _clock.now();

  /// Restores the unfinished session after an app kill (if any).
  Future<LoggerState?> restore() async {
    if (state != null) return state;
    final row = await _store.activeSession();
    if (row == null || row.stateJson == null) return null;
    final restored = LoggerState.fromJson(jsonDecode(row.stateJson!) as Map<String, dynamic>);
    state = restored;
    return restored;
  }

  /// Starts a workout (or resumes the in-progress one for the same workout).
  Future<LoggerState> start(Workout workout, {String? programDayId, String? assignmentId}) async {
    final existing = await restore();
    if (existing != null && existing.workoutId == workout.id) return existing;
    if (existing != null) {
      // A different workout was left open: keep its data, mark it abandoned.
      await _store.abandon(existing.sessionId);
    }
    final now = _now;
    final session = LoggerState.fromWorkout(
      workout,
      sessionId: Ulid.generate(),
      startedAt: now,
      newId: Ulid.generate,
      programDayId: programDayId,
      assignmentId: assignmentId,
    );
    await _store.start(
      id: session.sessionId,
      startedAt: now,
      workout: workout,
      programDayId: programDayId,
      programAssignmentId: assignmentId,
      stateJson: jsonEncode(session.toJson()),
    );
    state = session;
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.workoutStarted(workoutId: workout.id)));
    return session;
  }

  /// Applies [next], persisting the snapshot and the sets in [changedSetIds].
  Future<void> _commit(LoggerState next, {Iterable<String> changedSetIds = const []}) {
    final previousRest = state?.rest;
    state = next;
    final ids = changedSetIds.toList();
    _writes = _writes.then((_) async {
      await _store.saveState(next.sessionId, jsonEncode(next.toJson()));
      for (final id in ids) {
        final loc = next.locate(id);
        if (loc == null) continue;
        final exercise = next.blocks[loc.block].exercises[loc.exercise];
        await _store.saveSet(next.sessionId, next.toSessionSet(exercise, exercise.sets[loc.set]));
      }
    });
    if (previousRest?.endsAt != next.rest?.endsAt) unawaited(_scheduleRestAlarm(next.rest));
    return _writes;
  }

  /// Waits for pending writes (tests and finish).
  Future<void> flushWrites() => _writes;

  Future<void> _scheduleRestAlarm(RestTimer? rest) async {
    final notifier = ref.read(localNotifierProvider);
    await notifier.cancel(kRestNotificationId);
    if (rest == null || rest.isOver(_now)) return;
    await notifier.scheduleAt(
      id: kRestNotificationId,
      at: rest.endsAt,
      title: restAlarmTitle,
      body: restAlarmBody,
      payload: '/logger',
    );
  }

  /// Localized alarm strings (set by the logger screen).
  String restAlarmTitle = '';
  String restAlarmBody = '';

  LoggerState? get _s => state;

  Future<void> editWeight(String setId, double? kg) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.editSet(setId, weightKg: () => kg), changedSetIds: [setId]);
  }

  Future<void> editReps(String setId, int? reps) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.editSet(setId, reps: () => reps), changedSetIds: [setId]);
  }

  Future<void> editDuration(String setId, int? seconds) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.editSet(setId, durationSeconds: () => seconds), changedSetIds: [setId]);
  }

  Future<void> setRpe(String setId, double? rpe) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.editSet(setId, rpe: () => rpe), changedSetIds: [setId]);
  }

  Future<void> setType(String setId, SetType type) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.editSet(setId, setType: type), changedSetIds: [setId]);
  }

  Future<void> applyToRemaining(String exerciseId) async {
    final s = _s;
    if (s == null) return;
    final ids = s.exerciseById(exerciseId)?.sets.map((x) => x.id) ?? const <String>[];
    await _commit(s.applyToRemaining(exerciseId), changedSetIds: ids);
  }

  Future<void> dismissApplyPrompt() async {
    final s = _s;
    if (s == null) return;
    await _commit(s.dismissApplyPrompt());
  }

  Future<void> toggleSet(String setId) async {
    final s = _s;
    if (s == null) return;
    final wasDone = s.setById(setId)?.completed ?? false;
    await _commit(s.toggleSet(setId, _now), changedSetIds: [setId]);
    if (!wasDone) _onSetCompleted(setId);
  }

  Future<void> sameAsLast(String setId) async {
    final s = _s;
    if (s == null) return;
    final wasDone = s.setById(setId)?.completed ?? false;
    await _commit(s.sameAsLast(setId, _now), changedSetIds: [setId]);
    if (!wasDone) _onSetCompleted(setId);
  }

  void _onSetCompleted(String setId) {
    final exerciseId = state?.allExercises.firstWhere((e) => e.sets.any((x) => x.id == setId)).exercise.id;
    if (exerciseId != null) {
      unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.setCompleted(exerciseId: exerciseId)));
    }
  }

  Future<void> focusSet(String setId) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.focusSet(setId));
  }

  Future<void> addSet(String exerciseId) async {
    final s = _s;
    if (s == null) return;
    final id = Ulid.generate();
    await _commit(s.addSet(exerciseId, id), changedSetIds: [id]);
  }

  Future<void> removeSet(String setId) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.removeSet(setId));
    await _store.deleteSet(s.sessionId, setId);
  }

  Future<void> addExercise(Exercise exercise) async {
    final s = _s;
    if (s == null) return;
    LastPerformance? last;
    try {
      last = await ref.read(workoutRepositoryProvider).lastPerformance(exercise.id);
    } on ApiFailure {
      last = null;
    }
    await _commit(s.addExercise(exercise, newId: Ulid.generate, last: last));
  }

  Future<void> swapExercise(String exerciseId, Exercise replacement) async {
    final s = _s;
    if (s == null) return;
    LastPerformance? last;
    try {
      last = await ref.read(workoutRepositoryProvider).lastPerformance(replacement.id);
    } on ApiFailure {
      last = null;
    }
    final next = s.swapExercise(exerciseId, replacement, last: last);
    await _commit(next, changedSetIds: next.exerciseById(exerciseId)?.sets.map((x) => x.id) ?? const []);
  }

  Future<void> setExerciseNote(String exerciseId, String? note) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.setExerciseNote(exerciseId, note));
  }

  Future<void> startRest(int seconds) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.startRest(seconds, _now));
  }

  Future<void> adjustRest(Duration delta) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.adjustRest(delta, _now));
  }

  Future<void> skipRest() async {
    final s = _s;
    if (s == null) return;
    await _commit(s.skipRest());
  }

  /// Called by the UI ticker when the rest reaches zero (plays once).
  Future<void> onRestFinished() async {
    final s = _s;
    if (s == null || s.rest == null || s.rest!.alerted) return;
    await _commit(s.markRestAlerted());
    AuHaptics.heavy();
    unawaited(ref.read(audioCuesProvider).play(Cue.restOver));
  }

  Future<void> startClock(String blockId) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.startClock(blockId, _now));
    unawaited(ref.read(audioCuesProvider).play(Cue.go));
  }

  Future<void> pauseClock(String blockId) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.pauseClock(blockId, _now));
  }

  Future<void> resumeClock(String blockId) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.resumeClock(blockId, _now));
  }

  Future<void> resetClock(String blockId) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.resetClock(blockId));
  }

  Future<void> changeAmrapRounds(String blockId, int delta) async {
    final s = _s;
    if (s == null) return;
    AuHaptics.medium();
    await _commit(s.changeAmrapRounds(blockId, delta));
  }

  Future<void> setSessionNote(String? note) async {
    final s = _s;
    if (s == null) return;
    await _commit(s.setSessionNote(note));
  }

  SessionSummary? summary() => state == null ? null : SessionSummary.of(state!, _now);

  /// Finishes: persists locally, pushes the queue and asks the server for PRs.
  Future<FinishResult> finish({int? perceivedEffort, String? mood, String? notes}) async {
    final s = _s!;
    final now = _now;
    final summary = SessionSummary.of(s, now);
    await _writes;
    final combinedNotes = [s.sessionNote, notes].whereType<String>().where((n) => n.trim().isNotEmpty).join('\n');
    await _store.finish(
      s.sessionId,
      endedAt: now,
      perceivedEffort: perceivedEffort,
      mood: mood,
      notes: combinedNotes.isEmpty ? null : combinedNotes,
    );
    await ref.read(localNotifierProvider).cancel(kRestNotificationId);
    state = null;

    WorkoutSession? server;
    try {
      final report = await ref.read(syncEngineProvider).flush(ignoreBackoff: true);
      if (report.failure == null) {
        server = await ref.read(workoutRepositoryProvider).complete(
              s.sessionId,
              endedAt: now,
              perceivedEffort: perceivedEffort,
              mood: mood,
              notes: combinedNotes.isEmpty ? null : combinedNotes,
            );
        await _store.saveResult(s.sessionId, server);
      }
    } on ApiFailure {
      server = null;
    }
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.workoutCompleted(
          durationSeconds: summary.duration.inSeconds,
          volumeKg: summary.volumeKg,
          prs: server?.personalRecords.length ?? 0,
        )));
    unawaited(ref.read(audioCuesProvider).play(Cue.finish));
    return FinishResult(summary: summary, server: server);
  }

  /// Explicitly discards the session (user confirmed).
  Future<void> discard() async {
    final s = _s;
    if (s == null) return;
    await _writes;
    await _store.abandon(s.sessionId);
    await ref.read(localNotifierProvider).cancel(kRestNotificationId);
    state = null;
  }
}

final loggerControllerProvider = NotifierProvider<LoggerController, LoggerState?>(LoggerController.new);

/// The unfinished session stored on device (drives "Resume workout").
final activeLocalSessionProvider = StreamProvider<LocalSession?>((ref) => ref.watch(sessionStoreProvider).watchActiveSession());

/// Last finished result (for the finish screen after saving).
final lastFinishResultProvider = NotifierProvider<_FinishResultHolder, FinishResult?>(_FinishResultHolder.new);

class _FinishResultHolder extends Notifier<FinishResult?> {
  @override
  FinishResult? build() => null;

  void set(FinishResult? value) => state = value;
}
