import 'package:aurum_core/aurum_core.dart';
import 'package:collection/collection.dart';
import 'package:flutter/foundation.dart';

/// Where a set's prefilled values came from.
enum PrefillSource { last, template, session, none }

/// Default rest when neither the set, exercise nor block prescribes one.
const kDefaultRestSeconds = 90;

/// One set row in the logger.
@immutable
class LoggerSet {
  const LoggerSet({
    required this.id,
    required this.setNumber,
    this.setType = SetType.working,
    this.weightKg,
    this.reps,
    this.durationSeconds,
    this.distanceM,
    this.rpe,
    this.completed = false,
    this.completedAt,
    this.source = PrefillSource.none,
    this.repsMin,
    this.repsMax,
    this.restSeconds,
    this.previous,
  });

  factory LoggerSet.fromJson(Map<String, dynamic> json) => LoggerSet(
        id: json['id'] as String,
        setNumber: json['set_number'] as int,
        setType: SetType.values.firstWhere((t) => t.name == json['set_type'], orElse: () => SetType.working),
        weightKg: parseDouble(json['weight_kg']),
        reps: json['reps'] as int?,
        durationSeconds: json['duration_seconds'] as int?,
        distanceM: parseDouble(json['distance_m']),
        rpe: parseDouble(json['rpe']),
        completed: json['completed'] as bool? ?? false,
        completedAt: json['completed_at'] == null ? null : DateTime.parse(json['completed_at'] as String),
        source: PrefillSource.values.firstWhere((s) => s.name == json['source'], orElse: () => PrefillSource.none),
        repsMin: json['reps_min'] as int?,
        repsMax: json['reps_max'] as int?,
        restSeconds: json['rest_seconds'] as int?,
        previous: json['previous'] == null ? null : LastSet.fromJson((json['previous'] as Map).cast<String, dynamic>()),
      );

  final String id;
  final int setNumber;
  final SetType setType;
  final double? weightKg;
  final int? reps;
  final int? durationSeconds;
  final double? distanceM;
  final double? rpe;
  final bool completed;
  final DateTime? completedAt;
  final PrefillSource source;
  final int? repsMin;
  final int? repsMax;
  final int? restSeconds;

  /// Last session's values for this set number (drives "same as last").
  final LastSet? previous;

  double get volumeKg => completed ? (weightKg ?? 0) * (reps ?? 0) : 0;

  bool sameValuesAs(LoggerSet other) =>
      weightKg == other.weightKg && reps == other.reps && durationSeconds == other.durationSeconds;

  LoggerSet copyWith({
    int? setNumber,
    SetType? setType,
    double? Function()? weightKg,
    int? Function()? reps,
    int? Function()? durationSeconds,
    double? Function()? distanceM,
    double? Function()? rpe,
    bool? completed,
    DateTime? Function()? completedAt,
    PrefillSource? source,
    LastSet? Function()? previous,
  }) =>
      LoggerSet(
        id: id,
        setNumber: setNumber ?? this.setNumber,
        setType: setType ?? this.setType,
        weightKg: weightKg != null ? weightKg() : this.weightKg,
        reps: reps != null ? reps() : this.reps,
        durationSeconds: durationSeconds != null ? durationSeconds() : this.durationSeconds,
        distanceM: distanceM != null ? distanceM() : this.distanceM,
        rpe: rpe != null ? rpe() : this.rpe,
        completed: completed ?? this.completed,
        completedAt: completedAt != null ? completedAt() : this.completedAt,
        source: source ?? this.source,
        repsMin: repsMin,
        repsMax: repsMax,
        restSeconds: restSeconds,
        previous: previous != null ? previous() : this.previous,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'set_number': setNumber,
        'set_type': setType.name,
        'weight_kg': weightKg,
        'reps': reps,
        'duration_seconds': durationSeconds,
        'distance_m': distanceM,
        'rpe': rpe,
        'completed': completed,
        'completed_at': completedAt?.toUtc().toIso8601String(),
        'source': source.name,
        'reps_min': repsMin,
        'reps_max': repsMax,
        'rest_seconds': restSeconds,
        'previous': previous?.toJson(),
      };
}

/// An exercise in the logger (from the workout, or added by the user).
@immutable
class LoggerExercise {
  const LoggerExercise({
    required this.id,
    required this.exercise,
    required this.sets,
    this.workoutExerciseId,
    this.restSeconds,
    this.tempo,
    this.coachNotes,
    this.userNote,
    this.alternativeIds = const [],
    this.swappedFromName,
  });

  factory LoggerExercise.fromJson(Map<String, dynamic> json) => LoggerExercise(
        id: json['id'] as String,
        workoutExerciseId: json['workout_exercise_id'] as String?,
        exercise: Exercise.fromJson((json['exercise'] as Map).cast<String, dynamic>()),
        restSeconds: json['rest_seconds'] as int?,
        tempo: json['tempo'] as String?,
        coachNotes: json['coach_notes'] as String?,
        userNote: json['user_note'] as String?,
        alternativeIds: (json['alternative_ids'] as List? ?? const []).cast<String>(),
        swappedFromName: json['swapped_from_name'] as String?,
        sets: (json['sets'] as List).map((e) => LoggerSet.fromJson((e as Map).cast<String, dynamic>())).toList(),
      );

  final String id;
  final String? workoutExerciseId;
  final Exercise exercise;
  final int? restSeconds;
  final String? tempo;
  final String? coachNotes;
  final String? userNote;
  final List<String> alternativeIds;
  final String? swappedFromName;
  final List<LoggerSet> sets;

  bool get isDone => sets.isNotEmpty && sets.every((s) => s.completed);

  LoggerExercise copyWith({
    Exercise? exercise,
    List<LoggerSet>? sets,
    String? Function()? userNote,
    String? Function()? swappedFromName,
  }) =>
      LoggerExercise(
        id: id,
        workoutExerciseId: workoutExerciseId,
        exercise: exercise ?? this.exercise,
        sets: sets ?? this.sets,
        restSeconds: restSeconds,
        tempo: tempo,
        coachNotes: coachNotes,
        userNote: userNote != null ? userNote() : this.userNote,
        alternativeIds: alternativeIds,
        swappedFromName: swappedFromName != null ? swappedFromName() : this.swappedFromName,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'workout_exercise_id': workoutExerciseId,
        'exercise': exercise.toJson(),
        'rest_seconds': restSeconds,
        'tempo': tempo,
        'coach_notes': coachNotes,
        'user_note': userNote,
        'alternative_ids': alternativeIds,
        'swapped_from_name': swappedFromName,
        'sets': sets.map((s) => s.toJson()).toList(),
      };
}

/// A block of exercises with a training format.
@immutable
class LoggerBlock {
  const LoggerBlock({
    required this.id,
    required this.type,
    required this.exercises,
    this.name,
    this.rounds,
    this.workSeconds,
    this.restSeconds,
    this.restBetweenRounds,
    this.timeCapSeconds,
    this.notes,
  });

  factory LoggerBlock.fromJson(Map<String, dynamic> json) => LoggerBlock(
        id: json['id'] as String,
        type: BlockType.values.firstWhere((t) => t.name == json['type'], orElse: () => BlockType.straight),
        name: json['name'] as String?,
        rounds: json['rounds'] as int?,
        workSeconds: json['work_seconds'] as int?,
        restSeconds: json['rest_seconds'] as int?,
        restBetweenRounds: json['rest_between_rounds'] as int?,
        timeCapSeconds: json['time_cap_seconds'] as int?,
        notes: json['notes'] as String?,
        exercises: (json['exercises'] as List).map((e) => LoggerExercise.fromJson((e as Map).cast<String, dynamic>())).toList(),
      );

  final String id;
  final BlockType type;
  final String? name;
  final int? rounds;
  final int? workSeconds;
  final int? restSeconds;
  final int? restBetweenRounds;
  final int? timeCapSeconds;
  final String? notes;
  final List<LoggerExercise> exercises;

  LoggerBlock copyWith({List<LoggerExercise>? exercises}) => LoggerBlock(
        id: id,
        type: type,
        name: name,
        rounds: rounds,
        workSeconds: workSeconds,
        restSeconds: restSeconds,
        restBetweenRounds: restBetweenRounds,
        timeCapSeconds: timeCapSeconds,
        notes: notes,
        exercises: exercises ?? this.exercises,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'type': type.name,
        'name': name,
        'rounds': rounds,
        'work_seconds': workSeconds,
        'rest_seconds': restSeconds,
        'rest_between_rounds': restBetweenRounds,
        'time_cap_seconds': timeCapSeconds,
        'notes': notes,
        'exercises': exercises.map((e) => e.toJson()).toList(),
      };
}

/// A rest countdown anchored to wall-clock time: it keeps counting while the
/// app is backgrounded or killed and is never paused automatically.
@immutable
class RestTimer {
  const RestTimer({required this.startedAt, required this.endsAt, this.alerted = false});

  factory RestTimer.start(DateTime now, int seconds) =>
      RestTimer(startedAt: now, endsAt: now.add(Duration(seconds: seconds)));

  factory RestTimer.fromJson(Map<String, dynamic> json) => RestTimer(
        startedAt: DateTime.parse(json['started_at'] as String),
        endsAt: DateTime.parse(json['ends_at'] as String),
        alerted: json['alerted'] as bool? ?? false,
      );

  final DateTime startedAt;
  final DateTime endsAt;

  /// The end-of-rest cue was already played.
  final bool alerted;

  Duration get total => endsAt.difference(startedAt);

  Duration remaining(DateTime now) {
    final r = endsAt.difference(now);
    return r.isNegative ? Duration.zero : r;
  }

  bool isOver(DateTime now) => !now.isBefore(endsAt);

  /// Fraction remaining (1 → 0).
  double fraction(DateTime now) =>
      total.inMilliseconds <= 0 ? 0 : (remaining(now).inMilliseconds / total.inMilliseconds).clamp(0.0, 1.0);

  /// Adds/subtracts time; never ends in the past.
  RestTimer adjust(Duration delta, DateTime now) {
    var end = endsAt.add(delta);
    if (end.isBefore(now)) end = now;
    final start = end.difference(startedAt).isNegative ? end : startedAt;
    return RestTimer(startedAt: start, endsAt: end);
  }

  RestTimer markAlerted() => RestTimer(startedAt: startedAt, endsAt: endsAt, alerted: true);

  Map<String, dynamic> toJson() => {
        'started_at': startedAt.toUtc().toIso8601String(),
        'ends_at': endsAt.toUtc().toIso8601String(),
        'alerted': alerted,
      };
}

/// Stopwatch for a timed block (Tabata/EMOM/interval/AMRAP), wall-clock based.
/// Only the user can pause it.
@immutable
class BlockClock {
  const BlockClock({required this.startedAt, this.pausedAt, this.pausedMs = 0});

  factory BlockClock.fromJson(Map<String, dynamic> json) => BlockClock(
        startedAt: DateTime.parse(json['started_at'] as String),
        pausedAt: json['paused_at'] == null ? null : DateTime.parse(json['paused_at'] as String),
        pausedMs: json['paused_ms'] as int? ?? 0,
      );

  final DateTime startedAt;
  final DateTime? pausedAt;
  final int pausedMs;

  bool get isPaused => pausedAt != null;

  Duration elapsed(DateTime now) {
    final end = pausedAt ?? now;
    final ms = end.difference(startedAt).inMilliseconds - pausedMs;
    return Duration(milliseconds: ms < 0 ? 0 : ms);
  }

  BlockClock pause(DateTime now) => isPaused ? this : BlockClock(startedAt: startedAt, pausedAt: now, pausedMs: pausedMs);

  BlockClock resume(DateTime now) => !isPaused
      ? this
      : BlockClock(startedAt: startedAt, pausedMs: pausedMs + now.difference(pausedAt!).inMilliseconds);

  Map<String, dynamic> toJson() => {
        'started_at': startedAt.toUtc().toIso8601String(),
        'paused_at': pausedAt?.toUtc().toIso8601String(),
        'paused_ms': pausedMs,
      };
}

/// The current phase of an interval-type block.
@immutable
class IntervalPhase {
  const IntervalPhase({
    required this.round,
    required this.totalRounds,
    required this.isWork,
    required this.remaining,
    required this.duration,
    required this.exerciseIndex,
    required this.finished,
    required this.phaseIndex,
  });

  final int round;
  final int totalRounds;
  final bool isWork;
  final Duration remaining;
  final Duration duration;
  final int exerciseIndex;
  final bool finished;

  /// Monotonic index of the phase (used to detect transitions for cues).
  final int phaseIndex;
}

/// The schedule of a Tabata / EMOM / interval block.
@immutable
class IntervalPlan {
  const IntervalPlan(this.phases, this.rounds);

  factory IntervalPlan.forBlock(LoggerBlock block) {
    final n = block.exercises.isEmpty ? 1 : block.exercises.length;
    final (int rounds, int work, int rest) = switch (block.type) {
      BlockType.tabata => (block.rounds ?? 8, block.workSeconds ?? 20, block.restSeconds ?? 10),
      BlockType.emom => (block.rounds ?? ((block.timeCapSeconds ?? 600) ~/ 60), block.workSeconds ?? 60, 0),
      _ => (block.rounds ?? 5, block.workSeconds ?? 40, block.restSeconds ?? 20),
    };
    final phases = <(bool, int, int, int)>[];
    for (var r = 0; r < rounds; r++) {
      phases.add((true, work, r, r % n));
      final isLast = r == rounds - 1;
      if (rest > 0 && !isLast) phases.add((false, rest, r, (r + 1) % n));
    }
    return IntervalPlan(phases, rounds);
  }

  /// (isWork, seconds, roundIndex, exerciseIndex).
  final List<(bool, int, int, int)> phases;
  final int rounds;

  Duration get total => Duration(seconds: phases.fold(0, (s, p) => s + p.$2));

  IntervalPhase at(Duration elapsed) {
    var cursor = Duration.zero;
    for (var i = 0; i < phases.length; i++) {
      final (work, seconds, round, ex) = phases[i];
      final end = cursor + Duration(seconds: seconds);
      if (elapsed < end) {
        return IntervalPhase(
          round: round + 1,
          totalRounds: rounds,
          isWork: work,
          remaining: end - elapsed,
          duration: Duration(seconds: seconds),
          exerciseIndex: ex,
          finished: false,
          phaseIndex: i,
        );
      }
      cursor = end;
    }
    final last = phases.isEmpty ? (true, 0, 0, 0) : phases.last;
    return IntervalPhase(
      round: rounds,
      totalRounds: rounds,
      isWork: false,
      remaining: Duration.zero,
      duration: Duration(seconds: last.$2),
      exerciseIndex: last.$4,
      finished: true,
      phaseIndex: phases.length,
    );
  }
}

/// A position in the workout.
typedef SetRef = ({int block, int exercise, int set});

/// Complete, serializable state of an in-progress workout session.
@immutable
class LoggerState {
  const LoggerState({
    required this.sessionId,
    required this.startedAt,
    required this.blocks,
    this.workoutId,
    this.programDayId,
    this.assignmentId,
    this.name,
    this.rest,
    this.clocks = const {},
    this.amrapRounds = const {},
    this.activeSetId,
    this.applyPromptExerciseId,
    this.sessionNote,
  });

  /// Builds the logger from a workout, prefilling every set from the last
  /// performance (same set number), falling back to template targets.
  factory LoggerState.fromWorkout(
    Workout workout, {
    required String sessionId,
    required DateTime startedAt,
    required String Function() newId,
    String? programDayId,
    String? assignmentId,
  }) {
    final blocks = [
      for (final block in workout.blocks)
        LoggerBlock(
          id: block.id,
          type: block.type,
          name: block.name,
          rounds: block.rounds,
          workSeconds: block.workSeconds,
          restSeconds: block.restSeconds,
          restBetweenRounds: block.restBetweenRounds,
          timeCapSeconds: block.timeCapSeconds,
          notes: block.notes,
          exercises: [
            for (final we in block.exercises)
              LoggerExercise(
                id: we.id,
                workoutExerciseId: we.id,
                exercise: we.exercise,
                restSeconds: we.restSeconds,
                tempo: we.tempo,
                coachNotes: we.notes,
                alternativeIds: we.alternativeIds,
                sets: prefillSets(
                  templates: we.sets.isEmpty ? const [SetTemplate()] : we.sets,
                  last: we.lastPerformance,
                  newId: newId,
                ),
              ),
          ],
        ),
    ];
    final state = LoggerState(
      sessionId: sessionId,
      startedAt: startedAt,
      blocks: blocks,
      workoutId: workout.id,
      programDayId: programDayId,
      assignmentId: assignmentId,
      name: workout.name,
    );
    return state.copyWith(activeSetId: () => state.orderedSetIds().firstOrNull);
  }

  factory LoggerState.fromJson(Map<String, dynamic> json) => LoggerState(
        sessionId: json['session_id'] as String,
        startedAt: DateTime.parse(json['started_at'] as String),
        workoutId: json['workout_id'] as String?,
        programDayId: json['program_day_id'] as String?,
        assignmentId: json['assignment_id'] as String?,
        name: json['name'] as String?,
        blocks: (json['blocks'] as List).map((e) => LoggerBlock.fromJson((e as Map).cast<String, dynamic>())).toList(),
        rest: json['rest'] == null ? null : RestTimer.fromJson((json['rest'] as Map).cast<String, dynamic>()),
        clocks: (json['clocks'] as Map? ?? const {})
            .map((k, v) => MapEntry(k as String, BlockClock.fromJson((v as Map).cast<String, dynamic>()))),
        amrapRounds: (json['amrap_rounds'] as Map? ?? const {}).map((k, v) => MapEntry(k as String, v as int)),
        activeSetId: json['active_set_id'] as String?,
        applyPromptExerciseId: json['apply_prompt_exercise_id'] as String?,
        sessionNote: json['session_note'] as String?,
      );

  /// Prefill rule: last performance for the same set number → template.
  static List<LoggerSet> prefillSets({
    required List<SetTemplate> templates,
    required LastPerformance? last,
    required String Function() newId,
  }) {
    final lastSets = last?.sets ?? const <LastSet>[];
    return [
      for (var i = 0; i < templates.length; i++)
        () {
          final t = templates[i];
          final prev = lastSets.firstWhereOrNull((s) => s.setNumber == i + 1) ?? (i < lastSets.length ? lastSets[i] : null);
          final fromLast = prev != null && (prev.reps != null || prev.weightKg != null || prev.durationSeconds != null);
          return LoggerSet(
            id: newId(),
            setNumber: i + 1,
            setType: t.setType,
            weightKg: fromLast ? prev.weightKg : t.weightKg,
            reps: fromLast ? prev.reps : (t.repsMax ?? t.repsMin),
            durationSeconds: fromLast ? prev.durationSeconds : t.durationSeconds,
            distanceM: fromLast ? prev.distanceM : t.distanceM,
            source: fromLast
                ? PrefillSource.last
                : (t.weightKg != null || t.repsMin != null || t.repsMax != null || t.durationSeconds != null)
                    ? PrefillSource.template
                    : PrefillSource.none,
            repsMin: t.repsMin,
            repsMax: t.repsMax,
            restSeconds: t.restSeconds,
            previous: prev,
          );
        }(),
    ];
  }

  final String sessionId;
  final DateTime startedAt;
  final String? workoutId;
  final String? programDayId;
  final String? assignmentId;
  final String? name;
  final List<LoggerBlock> blocks;
  final RestTimer? rest;
  final Map<String, BlockClock> clocks;
  final Map<String, int> amrapRounds;
  final String? activeSetId;

  /// When set, the UI offers "apply to remaining sets" for this exercise.
  final String? applyPromptExerciseId;
  final String? sessionNote;

  // ------------------------------------------------------------ Queries

  Iterable<LoggerExercise> get allExercises => blocks.expand((b) => b.exercises);
  Iterable<LoggerSet> get allSets => allExercises.expand((e) => e.sets);

  int get totalSets => allSets.length;
  int get completedSets => allSets.where((s) => s.completed).length;
  double get progress => totalSets == 0 ? 0 : completedSets / totalSets;
  double get totalVolumeKg => allSets.fold(0, (sum, s) => sum + s.volumeKg);
  bool get isComplete => totalSets > 0 && completedSets == totalSets;

  Duration elapsed(DateTime now) => now.difference(startedAt);

  SetRef? locate(String setId) {
    for (var b = 0; b < blocks.length; b++) {
      for (var e = 0; e < blocks[b].exercises.length; e++) {
        final i = blocks[b].exercises[e].sets.indexWhere((s) => s.id == setId);
        if (i >= 0) return (block: b, exercise: e, set: i);
      }
    }
    return null;
  }

  LoggerSet? setById(String id) {
    final r = locate(id);
    return r == null ? null : blocks[r.block].exercises[r.exercise].sets[r.set];
  }

  LoggerExercise? exerciseById(String id) => allExercises.firstWhereOrNull((e) => e.id == id);

  ({int block, int exercise})? locateExercise(String exerciseId) {
    for (var b = 0; b < blocks.length; b++) {
      final e = blocks[b].exercises.indexWhere((x) => x.id == exerciseId);
      if (e >= 0) return (block: b, exercise: e);
    }
    return null;
  }

  /// Logging order: sequential for straight blocks; for supersets / giant
  /// sets / circuits, rounds are interleaved (A1, B1, A2, B2 …).
  List<String> orderedSetIds() {
    final ids = <String>[];
    for (final block in blocks) {
      if (block.type.isCycling) {
        final maxSets = block.exercises.fold(0, (m, e) => e.sets.length > m ? e.sets.length : m);
        for (var r = 0; r < maxSets; r++) {
          for (final e in block.exercises) {
            if (r < e.sets.length) ids.add(e.sets[r].id);
          }
        }
      } else {
        for (final e in block.exercises) {
          ids.addAll(e.sets.map((s) => s.id));
        }
      }
    }
    return ids;
  }

  /// The next incomplete set after [setId] in logging order (wrapping).
  String? nextIncompleteAfter(String? setId) {
    final order = orderedSetIds();
    final start = setId == null ? -1 : order.indexOf(setId);
    for (var i = 1; i <= order.length; i++) {
      final id = order[(start + i) % order.length];
      if (!(setById(id)?.completed ?? true)) return id;
    }
    return null;
  }

  /// Rest to start after completing [setId], in seconds (0 = none).
  int restAfter(String setId) {
    final r = locate(setId);
    if (r == null) return 0;
    final block = blocks[r.block];
    final exercise = block.exercises[r.exercise];
    final set = exercise.sets[r.set];
    if (block.type.isTimed || block.type == BlockType.amrap) return 0;
    if (block.type.isCycling) {
      // Rest only after the last exercise of the round.
      final round = r.set;
      final lastInRound = block.exercises.lastIndexWhere((e) => round < e.sets.length);
      if (r.exercise != lastInRound) return 0;
      return block.restBetweenRounds ?? block.restSeconds ?? exercise.restSeconds ?? kDefaultRestSeconds;
    }
    return set.restSeconds ?? exercise.restSeconds ?? block.restSeconds ?? kDefaultRestSeconds;
  }

  /// Every logged/editable set as the API model.
  List<SessionSet> toSessionSets() => [
        for (final e in allExercises)
          for (final s in e.sets) toSessionSet(e, s),
      ];

  SessionSet toSessionSet(LoggerExercise e, LoggerSet s) => SessionSet(
        id: s.id,
        exerciseId: e.exercise.id,
        workoutExerciseId: e.workoutExerciseId,
        setNumber: s.setNumber,
        setType: s.setType,
        reps: s.reps,
        weightKg: s.weightKg,
        durationSeconds: s.durationSeconds,
        distanceM: s.distanceM,
        rpe: s.rpe,
        isCompleted: s.completed,
        completedAt: s.completedAt,
      );

  // ------------------------------------------------------------ Mutations

  LoggerState _replaceSet(String setId, LoggerSet Function(LoggerSet) update) {
    final r = locate(setId);
    if (r == null) return this;
    return _replaceExercise(blocks[r.block].exercises[r.exercise].id, (e) {
      final sets = [...e.sets];
      sets[r.set] = update(sets[r.set]);
      return e.copyWith(sets: sets);
    });
  }

  LoggerState _replaceExercise(String exerciseId, LoggerExercise Function(LoggerExercise) update) {
    final loc = locateExercise(exerciseId);
    if (loc == null) return this;
    final newBlocks = [...blocks];
    final exercises = [...newBlocks[loc.block].exercises];
    exercises[loc.exercise] = update(exercises[loc.exercise]);
    newBlocks[loc.block] = newBlocks[loc.block].copyWith(exercises: exercises);
    return copyWith(blocks: newBlocks);
  }

  /// Edits a set's values. Editing set 1 while later sets differ raises the
  /// "apply to remaining sets" prompt.
  LoggerState editSet(
    String setId, {
    double? Function()? weightKg,
    int? Function()? reps,
    int? Function()? durationSeconds,
    double? Function()? rpe,
    SetType? setType,
  }) {
    var next = _replaceSet(
      setId,
      (s) => s.copyWith(weightKg: weightKg, reps: reps, durationSeconds: durationSeconds, rpe: rpe, setType: setType),
    );
    final r = next.locate(setId);
    if (r != null && r.set == 0 && (weightKg != null || reps != null || durationSeconds != null)) {
      final exercise = next.blocks[r.block].exercises[r.exercise];
      final first = exercise.sets.first;
      final differs = exercise.sets.skip(1).any((s) => !s.completed && !s.sameValuesAs(first));
      next = next.copyWith(applyPromptExerciseId: () => differs ? exercise.id : null);
    }
    return next;
  }

  /// Copies set 1's values to every remaining (incomplete) set.
  LoggerState applyToRemaining(String exerciseId) {
    final next = _replaceExercise(exerciseId, (e) {
      if (e.sets.isEmpty) return e;
      final first = e.sets.first;
      return e.copyWith(sets: [
        first,
        for (final s in e.sets.skip(1))
          s.completed
              ? s
              : s.copyWith(
                  weightKg: () => first.weightKg,
                  reps: () => first.reps,
                  durationSeconds: () => first.durationSeconds,
                  source: PrefillSource.session,
                ),
      ]);
    });
    return next.copyWith(applyPromptExerciseId: () => null);
  }

  LoggerState dismissApplyPrompt() => copyWith(applyPromptExerciseId: () => null);

  /// Values for "same as last": last session's set, else this session's
  /// previous set of the same exercise.
  LoggerSet? sameAsLastSource(String setId) {
    final r = locate(setId);
    if (r == null) return null;
    final set = blocks[r.block].exercises[r.exercise].sets[r.set];
    final prev = set.previous;
    if (prev != null) {
      return set.copyWith(
        weightKg: () => prev.weightKg,
        reps: () => prev.reps,
        durationSeconds: () => prev.durationSeconds,
        source: PrefillSource.last,
      );
    }
    if (r.set > 0) {
      final before = blocks[r.block].exercises[r.exercise].sets[r.set - 1];
      return set.copyWith(
        weightKg: () => before.weightKg,
        reps: () => before.reps,
        durationSeconds: () => before.durationSeconds,
        source: PrefillSource.session,
      );
    }
    return null;
  }

  /// One tap: fill with last time's values and complete the set.
  LoggerState sameAsLast(String setId, DateTime now) {
    final source = sameAsLastSource(setId);
    if (source == null) return completeSet(setId, now);
    return _replaceSet(setId, (_) => source).completeSet(setId, now);
  }

  /// Completes a set, starts the rest timer and moves the cursor.
  LoggerState completeSet(String setId, DateTime now) {
    final set = setById(setId);
    if (set == null || set.completed) return this;
    var next = _replaceSet(setId, (s) => s.copyWith(completed: true, completedAt: () => now));
    final rest = next.restAfter(setId);
    next = next.copyWith(
      rest: () => rest > 0 ? RestTimer.start(now, rest) : null,
      activeSetId: () => next.nextIncompleteAfter(setId),
    );
    if (next.applyPromptExerciseId != null) {
      final r = next.locate(setId);
      if (r != null && next.blocks[r.block].exercises[r.exercise].id == next.applyPromptExerciseId) {
        next = next.copyWith(applyPromptExerciseId: () => null);
      }
    }
    return next;
  }

  LoggerState uncompleteSet(String setId) =>
      _replaceSet(setId, (s) => s.copyWith(completed: false, completedAt: () => null)).copyWith(activeSetId: () => setId);

  LoggerState toggleSet(String setId, DateTime now) =>
      (setById(setId)?.completed ?? false) ? uncompleteSet(setId) : completeSet(setId, now);

  LoggerState focusSet(String setId) => copyWith(activeSetId: () => setId);

  /// Adds a set copying the last set's values.
  LoggerState addSet(String exerciseId, String newId) => _replaceExercise(exerciseId, (e) {
        final last = e.sets.lastOrNull;
        return e.copyWith(sets: [
          ...e.sets,
          LoggerSet(
            id: newId,
            setNumber: e.sets.length + 1,
            setType: last?.setType == SetType.warmup ? SetType.working : (last?.setType ?? SetType.working),
            weightKg: last?.weightKg,
            reps: last?.reps,
            durationSeconds: last?.durationSeconds,
            source: last == null ? PrefillSource.none : PrefillSource.session,
            repsMin: last?.repsMin,
            repsMax: last?.repsMax,
            restSeconds: last?.restSeconds,
          ),
        ]);
      });

  /// Removes a set (the last remaining set of an exercise is kept).
  LoggerState removeSet(String setId) {
    final r = locate(setId);
    if (r == null) return this;
    final exercise = blocks[r.block].exercises[r.exercise];
    if (exercise.sets.length <= 1) return this;
    final next = _replaceExercise(exercise.id, (e) {
      final sets = [...e.sets]..removeAt(r.set);
      return e.copyWith(sets: [for (var i = 0; i < sets.length; i++) sets[i].copyWith(setNumber: i + 1)]);
    });
    return next.copyWith(activeSetId: () => activeSetId == setId ? next.nextIncompleteAfter(null) : activeSetId);
  }

  /// Appends an exercise as its own straight block.
  LoggerState addExercise(Exercise exercise, {required String Function() newId, LastPerformance? last, int sets = 3}) {
    final block = LoggerBlock(
      id: newId(),
      type: BlockType.straight,
      exercises: [
        LoggerExercise(
          id: newId(),
          exercise: exercise,
          sets: prefillSets(templates: List.filled(sets, const SetTemplate()), last: last, newId: newId),
        ),
      ],
    );
    final next = copyWith(blocks: [...blocks, block]);
    return next.copyWith(activeSetId: () => activeSetId ?? block.exercises.first.sets.first.id);
  }

  /// Swaps an exercise for an alternative; incomplete sets are re-prefilled
  /// from the new exercise's history (or keep the template targets).
  LoggerState swapExercise(String exerciseId, Exercise replacement, {LastPerformance? last}) =>
      _replaceExercise(exerciseId, (e) {
        final lastSets = last?.sets ?? const <LastSet>[];
        return e.copyWith(
          exercise: replacement,
          swappedFromName: () => e.swappedFromName ?? e.exercise.name,
          sets: [
            for (final s in e.sets)
              if (s.completed)
                s
              else
                () {
                  final prev = lastSets.firstWhereOrNull((l) => l.setNumber == s.setNumber);
                  return s.copyWith(
                    previous: () => prev,
                    weightKg: () => prev?.weightKg ?? (replacement.tracksWeight ? s.weightKg : null),
                    reps: () => prev?.reps ?? s.reps,
                    durationSeconds: () => prev?.durationSeconds ?? s.durationSeconds,
                    source: prev != null ? PrefillSource.last : PrefillSource.template,
                  );
                }(),
          ],
        );
      });

  LoggerState setExerciseNote(String exerciseId, String? note) =>
      _replaceExercise(exerciseId, (e) => e.copyWith(userNote: () => (note?.trim().isEmpty ?? true) ? null : note!.trim()));

  LoggerState setSessionNote(String? note) => copyWith(sessionNote: () => note);

  // Rest timer ----------------------------------------------------------

  LoggerState startRest(int seconds, DateTime now) => copyWith(rest: () => RestTimer.start(now, seconds));

  LoggerState adjustRest(Duration delta, DateTime now) =>
      rest == null ? this : copyWith(rest: () => rest!.adjust(delta, now));

  LoggerState skipRest() => copyWith(rest: () => null);

  LoggerState markRestAlerted() => rest == null ? this : copyWith(rest: () => rest!.markAlerted());

  // Block clocks ----------------------------------------------------------

  LoggerState startClock(String blockId, DateTime now) =>
      copyWith(clocks: {...clocks, blockId: BlockClock(startedAt: now)});

  LoggerState pauseClock(String blockId, DateTime now) {
    final clock = clocks[blockId];
    return clock == null ? this : copyWith(clocks: {...clocks, blockId: clock.pause(now)});
  }

  LoggerState resumeClock(String blockId, DateTime now) {
    final clock = clocks[blockId];
    return clock == null ? this : copyWith(clocks: {...clocks, blockId: clock.resume(now)});
  }

  LoggerState resetClock(String blockId) => copyWith(clocks: {...clocks}..remove(blockId));

  LoggerState changeAmrapRounds(String blockId, int delta) {
    final current = amrapRounds[blockId] ?? 0;
    final next = (current + delta).clamp(0, 999);
    return copyWith(amrapRounds: {...amrapRounds, blockId: next});
  }

  LoggerState copyWith({
    List<LoggerBlock>? blocks,
    RestTimer? Function()? rest,
    Map<String, BlockClock>? clocks,
    Map<String, int>? amrapRounds,
    String? Function()? activeSetId,
    String? Function()? applyPromptExerciseId,
    String? Function()? sessionNote,
  }) =>
      LoggerState(
        sessionId: sessionId,
        startedAt: startedAt,
        workoutId: workoutId,
        programDayId: programDayId,
        assignmentId: assignmentId,
        name: name,
        blocks: blocks ?? this.blocks,
        rest: rest != null ? rest() : this.rest,
        clocks: clocks ?? this.clocks,
        amrapRounds: amrapRounds ?? this.amrapRounds,
        activeSetId: activeSetId != null ? activeSetId() : this.activeSetId,
        applyPromptExerciseId: applyPromptExerciseId != null ? applyPromptExerciseId() : this.applyPromptExerciseId,
        sessionNote: sessionNote != null ? sessionNote() : this.sessionNote,
      );

  Map<String, dynamic> toJson() => {
        'session_id': sessionId,
        'started_at': startedAt.toUtc().toIso8601String(),
        'workout_id': workoutId,
        'program_day_id': programDayId,
        'assignment_id': assignmentId,
        'name': name,
        'blocks': blocks.map((b) => b.toJson()).toList(),
        'rest': rest?.toJson(),
        'clocks': clocks.map((k, v) => MapEntry(k, v.toJson())),
        'amrap_rounds': amrapRounds,
        'active_set_id': activeSetId,
        'apply_prompt_exercise_id': applyPromptExerciseId,
        'session_note': sessionNote,
      };
}
