// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'workouts.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_ExerciseVideo _$ExerciseVideoFromJson(Map<String, dynamic> json) =>
    _ExerciseVideo(
      provider: json['provider'] as String?,
      url: json['url'] as String?,
      posterUrl: json['poster_url'] as String?,
      previewUrl: json['preview_url'] as String?,
      status: json['status'] as String?,
    );

Map<String, dynamic> _$ExerciseVideoToJson(_ExerciseVideo instance) =>
    <String, dynamic>{
      'provider': instance.provider,
      'url': instance.url,
      'poster_url': instance.posterUrl,
      'preview_url': instance.previewUrl,
      'status': instance.status,
    };

_Exercise _$ExerciseFromJson(Map<String, dynamic> json) => _Exercise(
  id: json['id'] as String,
  slug: json['slug'] as String?,
  name: json['name'] as String,
  primaryMuscle: json['primary_muscle'] as String?,
  secondaryMuscles:
      (json['secondary_muscles'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const <String>[],
  equipment:
      (json['equipment'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>[],
  pattern: json['pattern'] as String?,
  mechanics: json['mechanics'] as String?,
  force: json['force'] as String?,
  level: json['level'] as String?,
  isUnilateral: json['is_unilateral'] as bool? ?? false,
  isBodyweight: json['is_bodyweight'] as bool? ?? false,
  metrics:
      (json['metrics'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>['weight', 'reps'],
  video: json['video'] == null
      ? null
      : ExerciseVideo.fromJson(json['video'] as Map<String, dynamic>),
  instructions:
      (json['instructions'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const <String>[],
  tips:
      (json['tips'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>[],
  mistakes:
      (json['mistakes'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>[],
  alternatives:
      (json['alternatives'] as List<dynamic>?)
          ?.map((e) => IdName.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <IdName>[],
  isCustom: json['is_custom'] as bool? ?? false,
  isFavorite: json['is_favorite'] as bool? ?? false,
);

Map<String, dynamic> _$ExerciseToJson(_Exercise instance) => <String, dynamic>{
  'id': instance.id,
  'slug': instance.slug,
  'name': instance.name,
  'primary_muscle': instance.primaryMuscle,
  'secondary_muscles': instance.secondaryMuscles,
  'equipment': instance.equipment,
  'pattern': instance.pattern,
  'mechanics': instance.mechanics,
  'force': instance.force,
  'level': instance.level,
  'is_unilateral': instance.isUnilateral,
  'is_bodyweight': instance.isBodyweight,
  'metrics': instance.metrics,
  'video': instance.video?.toJson(),
  'instructions': instance.instructions,
  'tips': instance.tips,
  'mistakes': instance.mistakes,
  'alternatives': instance.alternatives.map((e) => e.toJson()).toList(),
  'is_custom': instance.isCustom,
  'is_favorite': instance.isFavorite,
};

_SetTemplate _$SetTemplateFromJson(Map<String, dynamic> json) => _SetTemplate(
  id: json['id'] as String?,
  position: (json['position'] as num?)?.toInt() ?? 1,
  setType:
      $enumDecodeNullable(
        _$SetTypeEnumMap,
        json['set_type'],
        unknownValue: SetType.working,
      ) ??
      SetType.working,
  repsMin: (json['reps_min'] as num?)?.toInt(),
  repsMax: (json['reps_max'] as num?)?.toInt(),
  weightKg: const JsonDoubleN().fromJson(json['weight_kg']),
  percent1rm: const JsonDoubleN().fromJson(json['percent_1rm']),
  rpe: const JsonDoubleN().fromJson(json['rpe']),
  rir: (json['rir'] as num?)?.toInt(),
  durationSeconds: (json['duration_seconds'] as num?)?.toInt(),
  distanceM: const JsonDoubleN().fromJson(json['distance_m']),
  restSeconds: (json['rest_seconds'] as num?)?.toInt(),
);

Map<String, dynamic> _$SetTemplateToJson(_SetTemplate instance) =>
    <String, dynamic>{
      'id': instance.id,
      'position': instance.position,
      'set_type': _$SetTypeEnumMap[instance.setType]!,
      'reps_min': instance.repsMin,
      'reps_max': instance.repsMax,
      'weight_kg': const JsonDoubleN().toJson(instance.weightKg),
      'percent_1rm': const JsonDoubleN().toJson(instance.percent1rm),
      'rpe': const JsonDoubleN().toJson(instance.rpe),
      'rir': instance.rir,
      'duration_seconds': instance.durationSeconds,
      'distance_m': const JsonDoubleN().toJson(instance.distanceM),
      'rest_seconds': instance.restSeconds,
    };

const _$SetTypeEnumMap = {
  SetType.warmup: 'warmup',
  SetType.working: 'working',
  SetType.drop: 'drop',
  SetType.failure: 'failure',
  SetType.amrap: 'amrap',
};

_LastSet _$LastSetFromJson(Map<String, dynamic> json) => _LastSet(
  setNumber: (json['set_number'] as num).toInt(),
  reps: (json['reps'] as num?)?.toInt(),
  weightKg: const JsonDoubleN().fromJson(json['weight_kg']),
  durationSeconds: (json['duration_seconds'] as num?)?.toInt(),
  distanceM: const JsonDoubleN().fromJson(json['distance_m']),
  rpe: const JsonDoubleN().fromJson(json['rpe']),
);

Map<String, dynamic> _$LastSetToJson(_LastSet instance) => <String, dynamic>{
  'set_number': instance.setNumber,
  'reps': instance.reps,
  'weight_kg': const JsonDoubleN().toJson(instance.weightKg),
  'duration_seconds': instance.durationSeconds,
  'distance_m': const JsonDoubleN().toJson(instance.distanceM),
  'rpe': const JsonDoubleN().toJson(instance.rpe),
};

_LastPerformance _$LastPerformanceFromJson(Map<String, dynamic> json) =>
    _LastPerformance(
      sessionId: json['session_id'] as String,
      performedAt: DateTime.parse(json['performed_at'] as String),
      sets:
          (json['sets'] as List<dynamic>?)
              ?.map((e) => LastSet.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <LastSet>[],
    );

Map<String, dynamic> _$LastPerformanceToJson(_LastPerformance instance) =>
    <String, dynamic>{
      'session_id': instance.sessionId,
      'performed_at': instance.performedAt.toIso8601String(),
      'sets': instance.sets.map((e) => e.toJson()).toList(),
    };

_WorkoutExercise _$WorkoutExerciseFromJson(Map<String, dynamic> json) =>
    _WorkoutExercise(
      id: json['id'] as String,
      position: (json['position'] as num?)?.toInt() ?? 1,
      exercise: Exercise.fromJson(json['exercise'] as Map<String, dynamic>),
      restSeconds: (json['rest_seconds'] as num?)?.toInt(),
      tempo: json['tempo'] as String?,
      notes: json['notes'] as String?,
      alternativeIds:
          (json['alternative_ids'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const <String>[],
      sets:
          (json['sets'] as List<dynamic>?)
              ?.map((e) => SetTemplate.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <SetTemplate>[],
      lastPerformance: json['last_performance'] == null
          ? null
          : LastPerformance.fromJson(
              json['last_performance'] as Map<String, dynamic>,
            ),
    );

Map<String, dynamic> _$WorkoutExerciseToJson(_WorkoutExercise instance) =>
    <String, dynamic>{
      'id': instance.id,
      'position': instance.position,
      'exercise': instance.exercise.toJson(),
      'rest_seconds': instance.restSeconds,
      'tempo': instance.tempo,
      'notes': instance.notes,
      'alternative_ids': instance.alternativeIds,
      'sets': instance.sets.map((e) => e.toJson()).toList(),
      'last_performance': instance.lastPerformance?.toJson(),
    };

_WorkoutBlock _$WorkoutBlockFromJson(Map<String, dynamic> json) =>
    _WorkoutBlock(
      id: json['id'] as String,
      position: (json['position'] as num?)?.toInt() ?? 1,
      type:
          $enumDecodeNullable(
            _$BlockTypeEnumMap,
            json['type'],
            unknownValue: BlockType.straight,
          ) ??
          BlockType.straight,
      name: json['name'] as String?,
      rounds: (json['rounds'] as num?)?.toInt(),
      workSeconds: (json['work_seconds'] as num?)?.toInt(),
      restSeconds: (json['rest_seconds'] as num?)?.toInt(),
      restBetweenRounds: (json['rest_between_rounds'] as num?)?.toInt(),
      timeCapSeconds: (json['time_cap_seconds'] as num?)?.toInt(),
      notes: json['notes'] as String?,
      exercises:
          (json['exercises'] as List<dynamic>?)
              ?.map((e) => WorkoutExercise.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <WorkoutExercise>[],
    );

Map<String, dynamic> _$WorkoutBlockToJson(_WorkoutBlock instance) =>
    <String, dynamic>{
      'id': instance.id,
      'position': instance.position,
      'type': _$BlockTypeEnumMap[instance.type]!,
      'name': instance.name,
      'rounds': instance.rounds,
      'work_seconds': instance.workSeconds,
      'rest_seconds': instance.restSeconds,
      'rest_between_rounds': instance.restBetweenRounds,
      'time_cap_seconds': instance.timeCapSeconds,
      'notes': instance.notes,
      'exercises': instance.exercises.map((e) => e.toJson()).toList(),
    };

const _$BlockTypeEnumMap = {
  BlockType.straight: 'straight',
  BlockType.superset: 'superset',
  BlockType.giant: 'giant',
  BlockType.circuit: 'circuit',
  BlockType.tabata: 'tabata',
  BlockType.emom: 'emom',
  BlockType.amrap: 'amrap',
  BlockType.interval: 'interval',
  BlockType.warmup: 'warmup',
  BlockType.cooldown: 'cooldown',
};

_Workout _$WorkoutFromJson(Map<String, dynamic> json) => _Workout(
  id: json['id'] as String,
  name: json['name'] as String,
  description: json['description'] as String?,
  difficulty: json['difficulty'] as String?,
  estimatedMinutes: (json['estimated_minutes'] as num?)?.toInt(),
  tags:
      (json['tags'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>[],
  equipmentSummary:
      (json['equipment_summary'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const <String>[],
  blocks:
      (json['blocks'] as List<dynamic>?)
          ?.map((e) => WorkoutBlock.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <WorkoutBlock>[],
  updatedAt: json['updated_at'] == null
      ? null
      : DateTime.parse(json['updated_at'] as String),
  downloadSizeBytes: (json['download_size_bytes'] as num?)?.toInt(),
);

Map<String, dynamic> _$WorkoutToJson(_Workout instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'description': instance.description,
  'difficulty': instance.difficulty,
  'estimated_minutes': instance.estimatedMinutes,
  'tags': instance.tags,
  'equipment_summary': instance.equipmentSummary,
  'blocks': instance.blocks.map((e) => e.toJson()).toList(),
  'updated_at': instance.updatedAt?.toIso8601String(),
  'download_size_bytes': instance.downloadSizeBytes,
};

_WorkoutRef _$WorkoutRefFromJson(Map<String, dynamic> json) => _WorkoutRef(
  id: json['id'] as String,
  name: json['name'] as String,
  estimatedMinutes: (json['estimated_minutes'] as num?)?.toInt(),
);

Map<String, dynamic> _$WorkoutRefToJson(_WorkoutRef instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'estimated_minutes': instance.estimatedMinutes,
    };

_ProgramDay _$ProgramDayFromJson(Map<String, dynamic> json) => _ProgramDay(
  id: json['id'] as String,
  dayNumber: (json['day_number'] as num).toInt(),
  title: json['title'] as String?,
  isRest: json['is_rest'] as bool? ?? false,
  workout: json['workout'] == null
      ? null
      : WorkoutRef.fromJson(json['workout'] as Map<String, dynamic>),
  mealPlanId: json['meal_plan_id'] as String?,
  habitIds:
      (json['habit_ids'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>[],
);

Map<String, dynamic> _$ProgramDayToJson(_ProgramDay instance) =>
    <String, dynamic>{
      'id': instance.id,
      'day_number': instance.dayNumber,
      'title': instance.title,
      'is_rest': instance.isRest,
      'workout': instance.workout?.toJson(),
      'meal_plan_id': instance.mealPlanId,
      'habit_ids': instance.habitIds,
    };

_ProgramWeek _$ProgramWeekFromJson(Map<String, dynamic> json) => _ProgramWeek(
  id: json['id'] as String,
  number: (json['number'] as num).toInt(),
  phase: json['phase'] as String?,
  isDeload: json['is_deload'] as bool? ?? false,
  notes: json['notes'] as String?,
  days:
      (json['days'] as List<dynamic>?)
          ?.map((e) => ProgramDay.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <ProgramDay>[],
);

Map<String, dynamic> _$ProgramWeekToJson(_ProgramWeek instance) =>
    <String, dynamic>{
      'id': instance.id,
      'number': instance.number,
      'phase': instance.phase,
      'is_deload': instance.isDeload,
      'notes': instance.notes,
      'days': instance.days.map((e) => e.toJson()).toList(),
    };

_Program _$ProgramFromJson(Map<String, dynamic> json) => _Program(
  id: json['id'] as String,
  name: json['name'] as String,
  description: json['description'] as String?,
  goal: json['goal'] as String?,
  level: json['level'] as String?,
  daysPerWeek: (json['days_per_week'] as num?)?.toInt(),
  equipment:
      (json['equipment'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>[],
  tags:
      (json['tags'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>[],
  visibility: json['visibility'] as String?,
  isTemplate: json['is_template'] as bool? ?? false,
  weeksCount: (json['weeks_count'] as num?)?.toInt(),
  weeks:
      (json['weeks'] as List<dynamic>?)
          ?.map((e) => ProgramWeek.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <ProgramWeek>[],
);

Map<String, dynamic> _$ProgramToJson(_Program instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'description': instance.description,
  'goal': instance.goal,
  'level': instance.level,
  'days_per_week': instance.daysPerWeek,
  'equipment': instance.equipment,
  'tags': instance.tags,
  'visibility': instance.visibility,
  'is_template': instance.isTemplate,
  'weeks_count': instance.weeksCount,
  'weeks': instance.weeks.map((e) => e.toJson()).toList(),
};

_ProgramAssignment _$ProgramAssignmentFromJson(Map<String, dynamic> json) =>
    _ProgramAssignment(
      id: json['id'] as String,
      program: IdName.fromJson(json['program'] as Map<String, dynamic>),
      client: json['client'] == null
          ? null
          : IdName.fromJson(json['client'] as Map<String, dynamic>),
      startDate: json['start_date'] as String,
      status: json['status'] as String? ?? 'active',
      unlockMode: json['unlock_mode'] as String? ?? 'all',
      currentWeek: (json['current_week'] as num?)?.toInt() ?? 1,
      progressPct: json['progress_pct'] == null
          ? 0
          : const JsonDouble().fromJson(json['progress_pct']),
    );

Map<String, dynamic> _$ProgramAssignmentToJson(_ProgramAssignment instance) =>
    <String, dynamic>{
      'id': instance.id,
      'program': instance.program.toJson(),
      'client': instance.client?.toJson(),
      'start_date': instance.startDate,
      'status': instance.status,
      'unlock_mode': instance.unlockMode,
      'current_week': instance.currentWeek,
      'progress_pct': const JsonDouble().toJson(instance.progressPct),
    };

_MyProgram _$MyProgramFromJson(Map<String, dynamic> json) => _MyProgram(
  assignment: ProgramAssignment.fromJson(
    json['assignment'] as Map<String, dynamic>,
  ),
  program: Program.fromJson(json['program'] as Map<String, dynamic>),
  unlockedThrough: json['unlocked_through'] as String?,
);

Map<String, dynamic> _$MyProgramToJson(_MyProgram instance) =>
    <String, dynamic>{
      'assignment': instance.assignment.toJson(),
      'program': instance.program.toJson(),
      'unlocked_through': instance.unlockedThrough,
    };
