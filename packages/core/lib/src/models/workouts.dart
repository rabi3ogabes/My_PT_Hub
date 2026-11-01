import 'package:freezed_annotation/freezed_annotation.dart';

import '../util/json_converters.dart';
import 'common.dart';

part 'workouts.freezed.dart';
part 'workouts.g.dart';

@JsonEnum(fieldRename: FieldRename.snake)
enum BlockType {
  straight,
  superset,
  giant,
  circuit,
  tabata,
  emom,
  amrap,
  interval,
  warmup,
  cooldown;

  /// Blocks whose exercises are performed in rotation (A1 → A2 → A1 …).
  bool get isCycling => this == superset || this == giant || this == circuit;

  /// Blocks driven by a work/rest clock rather than set completion.
  bool get isTimed => this == tabata || this == emom || this == interval;
}

@JsonEnum(fieldRename: FieldRename.snake)
enum SetType { warmup, working, drop, failure, amrap }

@freezed
abstract class ExerciseVideo with _$ExerciseVideo {
  const factory ExerciseVideo({String? provider, String? url, String? posterUrl, String? previewUrl, String? status}) =
      _ExerciseVideo;

  factory ExerciseVideo.fromJson(Map<String, dynamic> json) => _$ExerciseVideoFromJson(json);
}

/// **Exercise** — list items omit instructions/tips/mistakes/alternatives.
@freezed
abstract class Exercise with _$Exercise {
  const Exercise._();

  const factory Exercise({
    required String id,
    String? slug,
    required String name,
    String? primaryMuscle,
    @Default(<String>[]) List<String> secondaryMuscles,
    @Default(<String>[]) List<String> equipment,
    String? pattern,
    String? mechanics,
    String? force,
    String? level,
    @Default(false) bool isUnilateral,
    @Default(false) bool isBodyweight,
    @Default(<String>['weight', 'reps']) List<String> metrics,
    ExerciseVideo? video,
    @Default(<String>[]) List<String> instructions,
    @Default(<String>[]) List<String> tips,
    @Default(<String>[]) List<String> mistakes,
    @Default(<IdName>[]) List<IdName> alternatives,
    @Default(false) bool isCustom,
    @Default(false) bool isFavorite,
  }) = _Exercise;

  factory Exercise.fromJson(Map<String, dynamic> json) => _$ExerciseFromJson(json);

  bool get tracksWeight => metrics.contains('weight') && !isBodyweight;
  bool get tracksReps => metrics.contains('reps');
  bool get tracksTime => metrics.contains('time') || metrics.contains('duration');
  bool get tracksDistance => metrics.contains('distance');
}

/// **SetTemplate** — the coach's prescription for a set.
@freezed
abstract class SetTemplate with _$SetTemplate {
  @JsonDouble()
  @JsonDoubleN()
  const factory SetTemplate({
    String? id,
    @Default(1) int position,
    @JsonKey(unknownEnumValue: SetType.working) @Default(SetType.working) SetType setType,
    int? repsMin,
    int? repsMax,
    double? weightKg,
    @JsonKey(name: 'percent_1rm') double? percent1rm,
    double? rpe,
    int? rir,
    int? durationSeconds,
    double? distanceM,
    int? restSeconds,
  }) = _SetTemplate;

  factory SetTemplate.fromJson(Map<String, dynamic> json) => _$SetTemplateFromJson(json);
}

/// One set of a previous performance, used for logger prefill.
@freezed
abstract class LastSet with _$LastSet {
  @JsonDouble()
  @JsonDoubleN()
  const factory LastSet({
    required int setNumber,
    int? reps,
    double? weightKg,
    int? durationSeconds,
    double? distanceM,
    double? rpe,
  }) = _LastSet;

  factory LastSet.fromJson(Map<String, dynamic> json) => _$LastSetFromJson(json);
}

/// **LastPerformance** `{session_id, performed_at, sets}`.
@freezed
abstract class LastPerformance with _$LastPerformance {
  const factory LastPerformance({
    required String sessionId,
    required DateTime performedAt,
    @Default(<LastSet>[]) List<LastSet> sets,
  }) = _LastPerformance;

  factory LastPerformance.fromJson(Map<String, dynamic> json) => _$LastPerformanceFromJson(json);
}

/// **WorkoutExercise** — an exercise placed in a block.
@freezed
abstract class WorkoutExercise with _$WorkoutExercise {
  const factory WorkoutExercise({
    required String id,
    @Default(1) int position,
    required Exercise exercise,
    int? restSeconds,
    String? tempo,
    String? notes,
    @Default(<String>[]) List<String> alternativeIds,
    @Default(<SetTemplate>[]) List<SetTemplate> sets,
    LastPerformance? lastPerformance,
  }) = _WorkoutExercise;

  factory WorkoutExercise.fromJson(Map<String, dynamic> json) => _$WorkoutExerciseFromJson(json);
}

/// **Block** — a group of exercises with a training format.
@freezed
abstract class WorkoutBlock with _$WorkoutBlock {
  const factory WorkoutBlock({
    required String id,
    @Default(1) int position,
    @JsonKey(unknownEnumValue: BlockType.straight) @Default(BlockType.straight) BlockType type,
    String? name,
    int? rounds,
    int? workSeconds,
    int? restSeconds,
    int? restBetweenRounds,
    int? timeCapSeconds,
    String? notes,
    @Default(<WorkoutExercise>[]) List<WorkoutExercise> exercises,
  }) = _WorkoutBlock;

  factory WorkoutBlock.fromJson(Map<String, dynamic> json) => _$WorkoutBlockFromJson(json);
}

/// **Workout** (+ `download_size_bytes` on `GET /me/workouts/{id}`).
@freezed
abstract class Workout with _$Workout {
  const Workout._();

  const factory Workout({
    required String id,
    required String name,
    String? description,
    String? difficulty,
    int? estimatedMinutes,
    @Default(<String>[]) List<String> tags,
    @Default(<String>[]) List<String> equipmentSummary,
    @Default(<WorkoutBlock>[]) List<WorkoutBlock> blocks,
    DateTime? updatedAt,
    int? downloadSizeBytes,
  }) = _Workout;

  factory Workout.fromJson(Map<String, dynamic> json) => _$WorkoutFromJson(json);

  int get exerciseCount => blocks.fold(0, (sum, b) => sum + b.exercises.length);
  int get setCount => blocks.fold(0, (sum, b) => sum + b.exercises.fold(0, (s, e) => s + e.sets.length));
}

@freezed
abstract class WorkoutRef with _$WorkoutRef {
  const factory WorkoutRef({required String id, required String name, int? estimatedMinutes}) = _WorkoutRef;

  factory WorkoutRef.fromJson(Map<String, dynamic> json) => _$WorkoutRefFromJson(json);
}

@freezed
abstract class ProgramDay with _$ProgramDay {
  const factory ProgramDay({
    required String id,
    required int dayNumber,
    String? title,
    @Default(false) bool isRest,
    WorkoutRef? workout,
    String? mealPlanId,
    @Default(<String>[]) List<String> habitIds,
  }) = _ProgramDay;

  factory ProgramDay.fromJson(Map<String, dynamic> json) => _$ProgramDayFromJson(json);
}

@freezed
abstract class ProgramWeek with _$ProgramWeek {
  const factory ProgramWeek({
    required String id,
    required int number,
    String? phase,
    @Default(false) bool isDeload,
    String? notes,
    @Default(<ProgramDay>[]) List<ProgramDay> days,
  }) = _ProgramWeek;

  factory ProgramWeek.fromJson(Map<String, dynamic> json) => _$ProgramWeekFromJson(json);
}

/// **Program**.
@freezed
abstract class Program with _$Program {
  const factory Program({
    required String id,
    required String name,
    String? description,
    String? goal,
    String? level,
    int? daysPerWeek,
    @Default(<String>[]) List<String> equipment,
    @Default(<String>[]) List<String> tags,
    String? visibility,
    @Default(false) bool isTemplate,
    int? weeksCount,
    @Default(<ProgramWeek>[]) List<ProgramWeek> weeks,
  }) = _Program;

  factory Program.fromJson(Map<String, dynamic> json) => _$ProgramFromJson(json);
}

/// **ProgramAssignment**.
@freezed
abstract class ProgramAssignment with _$ProgramAssignment {
  @JsonDouble()
  const factory ProgramAssignment({
    required String id,
    required IdName program,
    IdName? client,
    required String startDate,
    @Default('active') String status,
    @Default('all') String unlockMode,
    @Default(1) int currentWeek,
    @Default(0) double progressPct,
  }) = _ProgramAssignment;

  factory ProgramAssignment.fromJson(Map<String, dynamic> json) => _$ProgramAssignmentFromJson(json);
}

/// `GET /me/program` → `{assignment, program, unlocked_through}`.
@freezed
abstract class MyProgram with _$MyProgram {
  const factory MyProgram({required ProgramAssignment assignment, required Program program, String? unlockedThrough}) =
      _MyProgram;

  factory MyProgram.fromJson(Map<String, dynamic> json) => _$MyProgramFromJson(json);
}
