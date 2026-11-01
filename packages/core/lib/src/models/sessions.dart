import 'package:freezed_annotation/freezed_annotation.dart';

import '../util/json_converters.dart';
import 'workouts.dart';

part 'sessions.freezed.dart';
part 'sessions.g.dart';

@JsonEnum(fieldRename: FieldRename.snake)
enum SessionStatus { inProgress, completed, abandoned }

/// **SessionSet** — one logged set (append-only; client ULID id).
@freezed
abstract class SessionSet with _$SessionSet {
  @JsonDouble()
  @JsonDoubleN()
  const factory SessionSet({
    required String id,
    required String exerciseId,
    String? workoutExerciseId,
    required int setNumber,
    @JsonKey(unknownEnumValue: SetType.working) @Default(SetType.working) SetType setType,
    int? reps,
    double? weightKg,
    int? durationSeconds,
    double? distanceM,
    double? rpe,
    @Default(false) bool isCompleted,
    DateTime? completedAt,
  }) = _SessionSet;

  factory SessionSet.fromJson(Map<String, dynamic> json) => _$SessionSetFromJson(json);
}

/// **PR** — personal record.
@freezed
abstract class PersonalRecord with _$PersonalRecord {
  @JsonDouble()
  @JsonDoubleN()
  const factory PersonalRecord({
    required String id,
    required String exerciseId,
    required String exerciseName,
    required String type,
    required double value,
    double? previousValue,
    required DateTime achievedAt,
  }) = _PersonalRecord;

  factory PersonalRecord.fromJson(Map<String, dynamic> json) => _$PersonalRecordFromJson(json);
}

/// **Session** — a workout session.
@freezed
abstract class WorkoutSession with _$WorkoutSession {
  @JsonDoubleN()
  const factory WorkoutSession({
    required String id,
    String? workoutId,
    String? programDayId,
    String? name,
    @JsonKey(unknownEnumValue: SessionStatus.inProgress) @Default(SessionStatus.inProgress) SessionStatus status,
    DateTime? startedAt,
    DateTime? endedAt,
    int? durationSeconds,
    double? totalVolumeKg,
    int? perceivedEffort,
    String? mood,
    String? notes,
    @Default(<SessionSet>[]) List<SessionSet> sets,
    @Default(<PersonalRecord>[]) List<PersonalRecord> personalRecords,
  }) = _WorkoutSession;

  factory WorkoutSession.fromJson(Map<String, dynamic> json) => _$WorkoutSessionFromJson(json);
}

@freezed
abstract class TopSet with _$TopSet {
  @JsonDoubleN()
  const factory TopSet({int? reps, double? weightKg}) = _TopSet;

  factory TopSet.fromJson(Map<String, dynamic> json) => _$TopSetFromJson(json);
}

@freezed
abstract class ExerciseHistoryEntry with _$ExerciseHistoryEntry {
  @JsonDoubleN()
  const factory ExerciseHistoryEntry({
    required String sessionId,
    required String date,
    TopSet? topSet,
    double? volumeKg,
    double? e1rmKg,
  }) = _ExerciseHistoryEntry;

  factory ExerciseHistoryEntry.fromJson(Map<String, dynamic> json) => _$ExerciseHistoryEntryFromJson(json);
}

/// `GET /me/exercises/{id}/history`.
@freezed
abstract class ExerciseHistory with _$ExerciseHistory {
  @JsonDoubleN()
  const factory ExerciseHistory({
    double? bestE1rmKg,
    double? bestWeightKg,
    @Default(<ExerciseHistoryEntry>[]) List<ExerciseHistoryEntry> sessions,
  }) = _ExerciseHistory;

  factory ExerciseHistory.fromJson(Map<String, dynamic> json) => _$ExerciseHistoryFromJson(json);
}
