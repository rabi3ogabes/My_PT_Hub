import 'package:freezed_annotation/freezed_annotation.dart';

import '../util/json_converters.dart';
import 'today.dart';

part 'habits.freezed.dart';
part 'habits.g.dart';

@freezed
abstract class Habit with _$Habit {
  const factory Habit({
    required String id,
    required String name,
    String? icon,
    @Default('boolean') String type,
    String? unit,
  }) = _Habit;

  factory Habit.fromJson(Map<String, dynamic> json) => _$HabitFromJson(json);
}

/// Item of `GET /me/habits?date=`.
@freezed
abstract class HabitItem with _$HabitItem {
  const HabitItem._();

  @JsonDouble()
  @JsonDoubleN()
  const factory HabitItem({
    required String assignmentId,
    required Habit habit,
    double? target,
    @Default(true) bool scheduledToday,
    double? value,
    @Default(false) bool completed,
    @Default(Streak()) Streak streak,
    @JsonKey(name: 'completion_pct_30d') @Default(0) double completionPct30d,
    @Default(<String>[]) List<String> reminderTimes,
  }) = _HabitItem;

  factory HabitItem.fromJson(Map<String, dynamic> json) => _$HabitItemFromJson(json);

  bool get isBoolean => habit.type == 'boolean';

  double get progress {
    if (completed) return 1;
    final t = target;
    final v = value;
    if (t == null || t <= 0 || v == null) return 0;
    return (v / t).clamp(0, 1).toDouble();
  }
}
