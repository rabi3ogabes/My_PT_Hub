// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'today.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_TodayWorkout _$TodayWorkoutFromJson(Map<String, dynamic> json) =>
    _TodayWorkout(
      workoutId: json['workout_id'] as String,
      programDayId: json['program_day_id'] as String?,
      programAssignmentId: json['program_assignment_id'] as String?,
      name: json['name'] as String,
      estimatedMinutes: (json['estimated_minutes'] as num?)?.toInt(),
      exerciseCount: (json['exercise_count'] as num?)?.toInt() ?? 0,
      weekNumber: (json['week_number'] as num?)?.toInt(),
      dayNumber: (json['day_number'] as num?)?.toInt(),
      status: json['status'] as String? ?? 'pending',
      sessionId: json['session_id'] as String?,
    );

Map<String, dynamic> _$TodayWorkoutToJson(_TodayWorkout instance) =>
    <String, dynamic>{
      'workout_id': instance.workoutId,
      'program_day_id': instance.programDayId,
      'program_assignment_id': instance.programAssignmentId,
      'name': instance.name,
      'estimated_minutes': instance.estimatedMinutes,
      'exercise_count': instance.exerciseCount,
      'week_number': instance.weekNumber,
      'day_number': instance.dayNumber,
      'status': instance.status,
      'session_id': instance.sessionId,
    };

_Streak _$StreakFromJson(Map<String, dynamic> json) => _Streak(
  current: (json['current'] as num?)?.toInt() ?? 0,
  best: (json['best'] as num?)?.toInt() ?? 0,
);

Map<String, dynamic> _$StreakToJson(_Streak instance) => <String, dynamic>{
  'current': instance.current,
  'best': instance.best,
};

_TodayNutrition _$TodayNutritionFromJson(Map<String, dynamic> json) =>
    _TodayNutrition(
      targets: json['targets'] == null
          ? null
          : Macros.fromJson(json['targets'] as Map<String, dynamic>),
      consumed: json['consumed'] == null
          ? const Macros()
          : Macros.fromJson(json['consumed'] as Map<String, dynamic>),
      waterMl: json['water_ml'] == null
          ? 0
          : const JsonDouble().fromJson(json['water_ml']),
      waterTargetMl: json['water_target_ml'] == null
          ? 0
          : const JsonDouble().fromJson(json['water_target_ml']),
    );

Map<String, dynamic> _$TodayNutritionToJson(_TodayNutrition instance) =>
    <String, dynamic>{
      'targets': instance.targets?.toJson(),
      'consumed': instance.consumed.toJson(),
      'water_ml': const JsonDouble().toJson(instance.waterMl),
      'water_target_ml': const JsonDouble().toJson(instance.waterTargetMl),
    };

_HabitsSummary _$HabitsSummaryFromJson(Map<String, dynamic> json) =>
    _HabitsSummary(
      completed: (json['completed'] as num?)?.toInt() ?? 0,
      total: (json['total'] as num?)?.toInt() ?? 0,
    );

Map<String, dynamic> _$HabitsSummaryToJson(_HabitsSummary instance) =>
    <String, dynamic>{'completed': instance.completed, 'total': instance.total};

_CheckinDue _$CheckinDueFromJson(Map<String, dynamic> json) => _CheckinDue(
  id: json['id'] as String,
  dueOn: json['due_on'] as String,
  formName: json['form_name'] as String?,
);

Map<String, dynamic> _$CheckinDueToJson(_CheckinDue instance) =>
    <String, dynamic>{
      'id': instance.id,
      'due_on': instance.dueOn,
      'form_name': instance.formName,
    };

_Today _$TodayFromJson(Map<String, dynamic> json) => _Today(
  date: json['date'] as String,
  greetingName: json['greeting_name'] as String?,
  coach: json['coach'] == null
      ? null
      : PersonRef.fromJson(json['coach'] as Map<String, dynamic>),
  workout: json['workout'] == null
      ? null
      : TodayWorkout.fromJson(json['workout'] as Map<String, dynamic>),
  streak: json['streak'] == null
      ? const Streak()
      : Streak.fromJson(json['streak'] as Map<String, dynamic>),
  nextSession: json['next_session'] == null
      ? null
      : Booking.fromJson(json['next_session'] as Map<String, dynamic>),
  nutrition: json['nutrition'] == null
      ? const TodayNutrition()
      : TodayNutrition.fromJson(json['nutrition'] as Map<String, dynamic>),
  habits: json['habits'] == null
      ? const HabitsSummary()
      : HabitsSummary.fromJson(json['habits'] as Map<String, dynamic>),
  checkinDue: json['checkin_due'] == null
      ? null
      : CheckinDue.fromJson(json['checkin_due'] as Map<String, dynamic>),
  unreadMessages: (json['unread_messages'] as num?)?.toInt() ?? 0,
);

Map<String, dynamic> _$TodayToJson(_Today instance) => <String, dynamic>{
  'date': instance.date,
  'greeting_name': instance.greetingName,
  'coach': instance.coach?.toJson(),
  'workout': instance.workout?.toJson(),
  'streak': instance.streak.toJson(),
  'next_session': instance.nextSession?.toJson(),
  'nutrition': instance.nutrition.toJson(),
  'habits': instance.habits.toJson(),
  'checkin_due': instance.checkinDue?.toJson(),
  'unread_messages': instance.unreadMessages,
};
