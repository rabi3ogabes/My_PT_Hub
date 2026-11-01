// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'habits.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_Habit _$HabitFromJson(Map<String, dynamic> json) => _Habit(
  id: json['id'] as String,
  name: json['name'] as String,
  icon: json['icon'] as String?,
  type: json['type'] as String? ?? 'boolean',
  unit: json['unit'] as String?,
);

Map<String, dynamic> _$HabitToJson(_Habit instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'icon': instance.icon,
  'type': instance.type,
  'unit': instance.unit,
};

_HabitItem _$HabitItemFromJson(Map<String, dynamic> json) => _HabitItem(
  assignmentId: json['assignment_id'] as String,
  habit: Habit.fromJson(json['habit'] as Map<String, dynamic>),
  target: const JsonDoubleN().fromJson(json['target']),
  scheduledToday: json['scheduled_today'] as bool? ?? true,
  value: const JsonDoubleN().fromJson(json['value']),
  completed: json['completed'] as bool? ?? false,
  streak: json['streak'] == null
      ? const Streak()
      : Streak.fromJson(json['streak'] as Map<String, dynamic>),
  completionPct30d: json['completion_pct_30d'] == null
      ? 0
      : const JsonDouble().fromJson(json['completion_pct_30d']),
  reminderTimes:
      (json['reminder_times'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList() ??
      const <String>[],
);

Map<String, dynamic> _$HabitItemToJson(
  _HabitItem instance,
) => <String, dynamic>{
  'assignment_id': instance.assignmentId,
  'habit': instance.habit.toJson(),
  'target': const JsonDoubleN().toJson(instance.target),
  'scheduled_today': instance.scheduledToday,
  'value': const JsonDoubleN().toJson(instance.value),
  'completed': instance.completed,
  'streak': instance.streak.toJson(),
  'completion_pct_30d': const JsonDouble().toJson(instance.completionPct30d),
  'reminder_times': instance.reminderTimes,
};
