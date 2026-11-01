import 'package:freezed_annotation/freezed_annotation.dart';

import '../util/json_converters.dart';
import 'calendar.dart';
import 'common.dart';
import 'nutrition.dart';

part 'today.freezed.dart';
part 'today.g.dart';

/// **TodayWorkout**.
@freezed
abstract class TodayWorkout with _$TodayWorkout {
  const factory TodayWorkout({
    required String workoutId,
    String? programDayId,
    String? programAssignmentId,
    required String name,
    int? estimatedMinutes,
    @Default(0) int exerciseCount,
    int? weekNumber,
    int? dayNumber,
    @Default('pending') String status,
    String? sessionId,
  }) = _TodayWorkout;

  factory TodayWorkout.fromJson(Map<String, dynamic> json) => _$TodayWorkoutFromJson(json);
}

@freezed
abstract class Streak with _$Streak {
  const factory Streak({@Default(0) int current, @Default(0) int best}) = _Streak;

  factory Streak.fromJson(Map<String, dynamic> json) => _$StreakFromJson(json);
}

@freezed
abstract class TodayNutrition with _$TodayNutrition {
  @JsonDouble()
  const factory TodayNutrition({
    Macros? targets,
    @Default(Macros()) Macros consumed,
    @Default(0) double waterMl,
    @Default(0) double waterTargetMl,
  }) = _TodayNutrition;

  factory TodayNutrition.fromJson(Map<String, dynamic> json) => _$TodayNutritionFromJson(json);
}

@freezed
abstract class HabitsSummary with _$HabitsSummary {
  const factory HabitsSummary({@Default(0) int completed, @Default(0) int total}) = _HabitsSummary;

  factory HabitsSummary.fromJson(Map<String, dynamic> json) => _$HabitsSummaryFromJson(json);
}

@freezed
abstract class CheckinDue with _$CheckinDue {
  const factory CheckinDue({required String id, required String dueOn, String? formName}) = _CheckinDue;

  factory CheckinDue.fromJson(Map<String, dynamic> json) => _$CheckinDueFromJson(json);
}

/// `GET /me/today?date=`.
@freezed
abstract class Today with _$Today {
  const factory Today({
    required String date,
    String? greetingName,
    PersonRef? coach,
    TodayWorkout? workout,
    @Default(Streak()) Streak streak,
    Booking? nextSession,
    @Default(TodayNutrition()) TodayNutrition nutrition,
    @Default(HabitsSummary()) HabitsSummary habits,
    CheckinDue? checkinDue,
    @Default(0) int unreadMessages,
  }) = _Today;

  factory Today.fromJson(Map<String, dynamic> json) => _$TodayFromJson(json);
}
