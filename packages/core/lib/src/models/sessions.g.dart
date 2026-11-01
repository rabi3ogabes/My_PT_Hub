// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'sessions.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_SessionSet _$SessionSetFromJson(Map<String, dynamic> json) => _SessionSet(
  id: json['id'] as String,
  exerciseId: json['exercise_id'] as String,
  workoutExerciseId: json['workout_exercise_id'] as String?,
  setNumber: (json['set_number'] as num).toInt(),
  setType:
      $enumDecodeNullable(
        _$SetTypeEnumMap,
        json['set_type'],
        unknownValue: SetType.working,
      ) ??
      SetType.working,
  reps: (json['reps'] as num?)?.toInt(),
  weightKg: const JsonDoubleN().fromJson(json['weight_kg']),
  durationSeconds: (json['duration_seconds'] as num?)?.toInt(),
  distanceM: const JsonDoubleN().fromJson(json['distance_m']),
  rpe: const JsonDoubleN().fromJson(json['rpe']),
  isCompleted: json['is_completed'] as bool? ?? false,
  completedAt: json['completed_at'] == null
      ? null
      : DateTime.parse(json['completed_at'] as String),
);

Map<String, dynamic> _$SessionSetToJson(_SessionSet instance) =>
    <String, dynamic>{
      'id': instance.id,
      'exercise_id': instance.exerciseId,
      'workout_exercise_id': instance.workoutExerciseId,
      'set_number': instance.setNumber,
      'set_type': _$SetTypeEnumMap[instance.setType]!,
      'reps': instance.reps,
      'weight_kg': const JsonDoubleN().toJson(instance.weightKg),
      'duration_seconds': instance.durationSeconds,
      'distance_m': const JsonDoubleN().toJson(instance.distanceM),
      'rpe': const JsonDoubleN().toJson(instance.rpe),
      'is_completed': instance.isCompleted,
      'completed_at': instance.completedAt?.toIso8601String(),
    };

const _$SetTypeEnumMap = {
  SetType.warmup: 'warmup',
  SetType.working: 'working',
  SetType.drop: 'drop',
  SetType.failure: 'failure',
  SetType.amrap: 'amrap',
};

_PersonalRecord _$PersonalRecordFromJson(Map<String, dynamic> json) =>
    _PersonalRecord(
      id: json['id'] as String,
      exerciseId: json['exercise_id'] as String,
      exerciseName: json['exercise_name'] as String,
      type: json['type'] as String,
      value: const JsonDouble().fromJson(json['value']),
      previousValue: const JsonDoubleN().fromJson(json['previous_value']),
      achievedAt: DateTime.parse(json['achieved_at'] as String),
    );

Map<String, dynamic> _$PersonalRecordToJson(_PersonalRecord instance) =>
    <String, dynamic>{
      'id': instance.id,
      'exercise_id': instance.exerciseId,
      'exercise_name': instance.exerciseName,
      'type': instance.type,
      'value': const JsonDouble().toJson(instance.value),
      'previous_value': const JsonDoubleN().toJson(instance.previousValue),
      'achieved_at': instance.achievedAt.toIso8601String(),
    };

_WorkoutSession _$WorkoutSessionFromJson(Map<String, dynamic> json) =>
    _WorkoutSession(
      id: json['id'] as String,
      workoutId: json['workout_id'] as String?,
      programDayId: json['program_day_id'] as String?,
      name: json['name'] as String?,
      status:
          $enumDecodeNullable(
            _$SessionStatusEnumMap,
            json['status'],
            unknownValue: SessionStatus.inProgress,
          ) ??
          SessionStatus.inProgress,
      startedAt: json['started_at'] == null
          ? null
          : DateTime.parse(json['started_at'] as String),
      endedAt: json['ended_at'] == null
          ? null
          : DateTime.parse(json['ended_at'] as String),
      durationSeconds: (json['duration_seconds'] as num?)?.toInt(),
      totalVolumeKg: const JsonDoubleN().fromJson(json['total_volume_kg']),
      perceivedEffort: (json['perceived_effort'] as num?)?.toInt(),
      mood: json['mood'] as String?,
      notes: json['notes'] as String?,
      sets:
          (json['sets'] as List<dynamic>?)
              ?.map((e) => SessionSet.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <SessionSet>[],
      personalRecords:
          (json['personal_records'] as List<dynamic>?)
              ?.map((e) => PersonalRecord.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <PersonalRecord>[],
    );

Map<String, dynamic> _$WorkoutSessionToJson(
  _WorkoutSession instance,
) => <String, dynamic>{
  'id': instance.id,
  'workout_id': instance.workoutId,
  'program_day_id': instance.programDayId,
  'name': instance.name,
  'status': _$SessionStatusEnumMap[instance.status]!,
  'started_at': instance.startedAt?.toIso8601String(),
  'ended_at': instance.endedAt?.toIso8601String(),
  'duration_seconds': instance.durationSeconds,
  'total_volume_kg': const JsonDoubleN().toJson(instance.totalVolumeKg),
  'perceived_effort': instance.perceivedEffort,
  'mood': instance.mood,
  'notes': instance.notes,
  'sets': instance.sets.map((e) => e.toJson()).toList(),
  'personal_records': instance.personalRecords.map((e) => e.toJson()).toList(),
};

const _$SessionStatusEnumMap = {
  SessionStatus.inProgress: 'in_progress',
  SessionStatus.completed: 'completed',
  SessionStatus.abandoned: 'abandoned',
};

_TopSet _$TopSetFromJson(Map<String, dynamic> json) => _TopSet(
  reps: (json['reps'] as num?)?.toInt(),
  weightKg: const JsonDoubleN().fromJson(json['weight_kg']),
);

Map<String, dynamic> _$TopSetToJson(_TopSet instance) => <String, dynamic>{
  'reps': instance.reps,
  'weight_kg': const JsonDoubleN().toJson(instance.weightKg),
};

_ExerciseHistoryEntry _$ExerciseHistoryEntryFromJson(
  Map<String, dynamic> json,
) => _ExerciseHistoryEntry(
  sessionId: json['session_id'] as String,
  date: json['date'] as String,
  topSet: json['top_set'] == null
      ? null
      : TopSet.fromJson(json['top_set'] as Map<String, dynamic>),
  volumeKg: const JsonDoubleN().fromJson(json['volume_kg']),
  e1rmKg: const JsonDoubleN().fromJson(json['e1rm_kg']),
);

Map<String, dynamic> _$ExerciseHistoryEntryToJson(
  _ExerciseHistoryEntry instance,
) => <String, dynamic>{
  'session_id': instance.sessionId,
  'date': instance.date,
  'top_set': instance.topSet?.toJson(),
  'volume_kg': const JsonDoubleN().toJson(instance.volumeKg),
  'e1rm_kg': const JsonDoubleN().toJson(instance.e1rmKg),
};

_ExerciseHistory _$ExerciseHistoryFromJson(Map<String, dynamic> json) =>
    _ExerciseHistory(
      bestE1rmKg: const JsonDoubleN().fromJson(json['best_e1rm_kg']),
      bestWeightKg: const JsonDoubleN().fromJson(json['best_weight_kg']),
      sessions:
          (json['sessions'] as List<dynamic>?)
              ?.map(
                (e) => ExerciseHistoryEntry.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          const <ExerciseHistoryEntry>[],
    );

Map<String, dynamic> _$ExerciseHistoryToJson(_ExerciseHistory instance) =>
    <String, dynamic>{
      'best_e1rm_kg': const JsonDoubleN().toJson(instance.bestE1rmKg),
      'best_weight_kg': const JsonDoubleN().toJson(instance.bestWeightKg),
      'sessions': instance.sessions.map((e) => e.toJson()).toList(),
    };
