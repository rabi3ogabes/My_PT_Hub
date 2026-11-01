import 'package:freezed_annotation/freezed_annotation.dart';

import '../util/json_converters.dart';

part 'nutrition.freezed.dart';
part 'nutrition.g.dart';

/// **Macros** `{kcal, protein_g, carbs_g, fat_g}`.
@freezed
abstract class Macros with _$Macros {
  const Macros._();

  @JsonDouble()
  const factory Macros({
    @Default(0) double kcal,
    @Default(0) double proteinG,
    @Default(0) double carbsG,
    @Default(0) double fatG,
  }) = _Macros;

  factory Macros.fromJson(Map<String, dynamic> json) => _$MacrosFromJson(json);

  static const zero = Macros();

  Macros operator +(Macros other) => Macros(
        kcal: kcal + other.kcal,
        proteinG: proteinG + other.proteinG,
        carbsG: carbsG + other.carbsG,
        fatG: fatG + other.fatG,
      );

  Macros scale(double factor) =>
      Macros(kcal: kcal * factor, proteinG: proteinG * factor, carbsG: carbsG * factor, fatG: fatG * factor);
}

@freezed
abstract class Nutrients with _$Nutrients {
  const Nutrients._();

  @JsonDouble()
  @JsonDoubleN()
  const factory Nutrients({
    @Default(0) double kcal,
    @Default(0) double proteinG,
    @Default(0) double carbsG,
    @Default(0) double fatG,
    double? fiberG,
    double? sugarG,
    double? sodiumMg,
  }) = _Nutrients;

  factory Nutrients.fromJson(Map<String, dynamic> json) => _$NutrientsFromJson(json);

  Macros get macros => Macros(kcal: kcal, proteinG: proteinG, carbsG: carbsG, fatG: fatG);
}

@freezed
abstract class FoodServing with _$FoodServing {
  @JsonDouble()
  const factory FoodServing({required String unit, required double grams, String? label}) = _FoodServing;

  factory FoodServing.fromJson(Map<String, dynamic> json) => _$FoodServingFromJson(json);
}

/// **Food**.
@freezed
abstract class Food with _$Food {
  const Food._();

  const factory Food({
    required String id,
    required String name,
    String? brand,
    String? barcode,
    String? source,
    String? region,
    String? category,
    @JsonKey(name: 'per_100g') @Default(Nutrients()) Nutrients per100g,
    @Default(<FoodServing>[]) List<FoodServing> servings,
    @Default(<String>[]) List<String> dietTags,
    @Default(<String>[]) List<String> allergens,
    @Default(false) bool isVerified,
    @Default(false) bool isCustom,
    @Default(false) bool isFavorite,
  }) = _Food;

  factory Food.fromJson(Map<String, dynamic> json) => _$FoodFromJson(json);

  /// Macros for [grams] of this food.
  Macros macrosFor(double grams) => per100g.macros.scale(grams / 100);
}

/// **FoodLog**.
@freezed
abstract class FoodLog with _$FoodLog {
  const FoodLog._();

  @JsonDouble()
  const factory FoodLog({
    required String id,
    required String logDate,
    required String slot,
    String? foodId,
    String? recipeId,
    required String name,
    @Default(0) double quantity,
    @Default('g') String unit,
    @Default(0) double kcal,
    @Default(0) double proteinG,
    @Default(0) double carbsG,
    @Default(0) double fatG,
    @Default('search') String source,
    DateTime? loggedAt,
  }) = _FoodLog;

  factory FoodLog.fromJson(Map<String, dynamic> json) => _$FoodLogFromJson(json);

  Macros get macros => Macros(kcal: kcal, proteinG: proteinG, carbsG: carbsG, fatG: fatG);
}

/// **PlannedItem**.
@freezed
abstract class PlannedItem with _$PlannedItem {
  const PlannedItem._();

  @JsonDouble()
  const factory PlannedItem({
    String? mealItemId,
    String? foodId,
    String? recipeId,
    required String name,
    @Default(0) double quantity,
    @Default('g') String unit,
    @Default(0) double kcal,
    @Default(0) double proteinG,
    @Default(0) double carbsG,
    @Default(0) double fatG,
    @Default(false) bool logged,
  }) = _PlannedItem;

  factory PlannedItem.fromJson(Map<String, dynamic> json) => _$PlannedItemFromJson(json);

  Macros get macros => Macros(kcal: kcal, proteinG: proteinG, carbsG: carbsG, fatG: fatG);
}

@freezed
abstract class MealSlot with _$MealSlot {
  const MealSlot._();

  const factory MealSlot({
    required String slot,
    required String label,
    @Default(Macros()) Macros consumed,
    @Default(<FoodLog>[]) List<FoodLog> logs,
    @Default(<PlannedItem>[]) List<PlannedItem> planned,
  }) = _MealSlot;

  factory MealSlot.fromJson(Map<String, dynamic> json) => _$MealSlotFromJson(json);

  bool get hasUnloggedPlan => planned.any((p) => !p.logged);
}

@freezed
abstract class Fasting with _$Fasting {
  const factory Fasting({@Default('none') String mode, String? windowStart, String? windowEnd}) = _Fasting;

  factory Fasting.fromJson(Map<String, dynamic> json) => _$FastingFromJson(json);
}

/// `GET /me/nutrition?date=`.
@freezed
abstract class NutritionDay with _$NutritionDay {
  @JsonDouble()
  const factory NutritionDay({
    required String date,
    Macros? targets,
    @Default(0) double waterMl,
    @Default(0) double waterTargetMl,
    @Default(Macros()) Macros consumed,
    @Default(<MealSlot>[]) List<MealSlot> meals,
    Fasting? fasting,
  }) = _NutritionDay;

  factory NutritionDay.fromJson(Map<String, dynamic> json) => _$NutritionDayFromJson(json);
}

@freezed
abstract class PlanMeal with _$PlanMeal {
  const factory PlanMeal({
    required String slot,
    String? name,
    String? time,
    @Default(<PlannedItem>[]) List<PlannedItem> items,
  }) = _PlanMeal;

  factory PlanMeal.fromJson(Map<String, dynamic> json) => _$PlanMealFromJson(json);
}

@freezed
abstract class PlanDay with _$PlanDay {
  const factory PlanDay({required int dayNumber, String? label, @Default(<PlanMeal>[]) List<PlanMeal> meals}) =
      _PlanDay;

  factory PlanDay.fromJson(Map<String, dynamic> json) => _$PlanDayFromJson(json);
}

/// `GET /me/nutrition/plan`.
@freezed
abstract class MealPlan with _$MealPlan {
  const factory MealPlan({required String id, required String name, String? type, @Default(<PlanDay>[]) List<PlanDay> days}) =
      _MealPlan;

  factory MealPlan.fromJson(Map<String, dynamic> json) => _$MealPlanFromJson(json);
}

/// One AI photo-recognition candidate.
@freezed
abstract class PhotoCandidate with _$PhotoCandidate {
  @JsonDouble()
  const factory PhotoCandidate({
    Food? food,
    required String name,
    @Default(0) double grams,
    @Default(0) double confidence,
    @Default(0) double kcal,
    @Default(0) double proteinG,
    @Default(0) double carbsG,
    @Default(0) double fatG,
  }) = _PhotoCandidate;

  factory PhotoCandidate.fromJson(Map<String, dynamic> json) => _$PhotoCandidateFromJson(json);
}

/// `POST /me/food-logs/photo` → `{candidates, ai_output_id}`.
@freezed
abstract class PhotoRecognition with _$PhotoRecognition {
  const factory PhotoRecognition({@Default(<PhotoCandidate>[]) List<PhotoCandidate> candidates, String? aiOutputId}) =
      _PhotoRecognition;

  factory PhotoRecognition.fromJson(Map<String, dynamic> json) => _$PhotoRecognitionFromJson(json);
}
