// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'nutrition.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_Macros _$MacrosFromJson(Map<String, dynamic> json) => _Macros(
  kcal: json['kcal'] == null ? 0 : const JsonDouble().fromJson(json['kcal']),
  proteinG: json['protein_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['protein_g']),
  carbsG: json['carbs_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['carbs_g']),
  fatG: json['fat_g'] == null ? 0 : const JsonDouble().fromJson(json['fat_g']),
);

Map<String, dynamic> _$MacrosToJson(_Macros instance) => <String, dynamic>{
  'kcal': const JsonDouble().toJson(instance.kcal),
  'protein_g': const JsonDouble().toJson(instance.proteinG),
  'carbs_g': const JsonDouble().toJson(instance.carbsG),
  'fat_g': const JsonDouble().toJson(instance.fatG),
};

_Nutrients _$NutrientsFromJson(Map<String, dynamic> json) => _Nutrients(
  kcal: json['kcal'] == null ? 0 : const JsonDouble().fromJson(json['kcal']),
  proteinG: json['protein_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['protein_g']),
  carbsG: json['carbs_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['carbs_g']),
  fatG: json['fat_g'] == null ? 0 : const JsonDouble().fromJson(json['fat_g']),
  fiberG: const JsonDoubleN().fromJson(json['fiber_g']),
  sugarG: const JsonDoubleN().fromJson(json['sugar_g']),
  sodiumMg: const JsonDoubleN().fromJson(json['sodium_mg']),
);

Map<String, dynamic> _$NutrientsToJson(_Nutrients instance) =>
    <String, dynamic>{
      'kcal': const JsonDouble().toJson(instance.kcal),
      'protein_g': const JsonDouble().toJson(instance.proteinG),
      'carbs_g': const JsonDouble().toJson(instance.carbsG),
      'fat_g': const JsonDouble().toJson(instance.fatG),
      'fiber_g': const JsonDoubleN().toJson(instance.fiberG),
      'sugar_g': const JsonDoubleN().toJson(instance.sugarG),
      'sodium_mg': const JsonDoubleN().toJson(instance.sodiumMg),
    };

_FoodServing _$FoodServingFromJson(Map<String, dynamic> json) => _FoodServing(
  unit: json['unit'] as String,
  grams: const JsonDouble().fromJson(json['grams']),
  label: json['label'] as String?,
);

Map<String, dynamic> _$FoodServingToJson(_FoodServing instance) =>
    <String, dynamic>{
      'unit': instance.unit,
      'grams': const JsonDouble().toJson(instance.grams),
      'label': instance.label,
    };

_Food _$FoodFromJson(Map<String, dynamic> json) => _Food(
  id: json['id'] as String,
  name: json['name'] as String,
  brand: json['brand'] as String?,
  barcode: json['barcode'] as String?,
  source: json['source'] as String?,
  region: json['region'] as String?,
  category: json['category'] as String?,
  per100g: json['per_100g'] == null
      ? const Nutrients()
      : Nutrients.fromJson(json['per_100g'] as Map<String, dynamic>),
  servings:
      (json['servings'] as List<dynamic>?)
          ?.map((e) => FoodServing.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <FoodServing>[],
  dietTags:
      (json['diet_tags'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>[],
  allergens:
      (json['allergens'] as List<dynamic>?)?.map((e) => e as String).toList() ??
      const <String>[],
  isVerified: json['is_verified'] as bool? ?? false,
  isCustom: json['is_custom'] as bool? ?? false,
  isFavorite: json['is_favorite'] as bool? ?? false,
);

Map<String, dynamic> _$FoodToJson(_Food instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'brand': instance.brand,
  'barcode': instance.barcode,
  'source': instance.source,
  'region': instance.region,
  'category': instance.category,
  'per_100g': instance.per100g.toJson(),
  'servings': instance.servings.map((e) => e.toJson()).toList(),
  'diet_tags': instance.dietTags,
  'allergens': instance.allergens,
  'is_verified': instance.isVerified,
  'is_custom': instance.isCustom,
  'is_favorite': instance.isFavorite,
};

_FoodLog _$FoodLogFromJson(Map<String, dynamic> json) => _FoodLog(
  id: json['id'] as String,
  logDate: json['log_date'] as String,
  slot: json['slot'] as String,
  foodId: json['food_id'] as String?,
  recipeId: json['recipe_id'] as String?,
  name: json['name'] as String,
  quantity: json['quantity'] == null
      ? 0
      : const JsonDouble().fromJson(json['quantity']),
  unit: json['unit'] as String? ?? 'g',
  kcal: json['kcal'] == null ? 0 : const JsonDouble().fromJson(json['kcal']),
  proteinG: json['protein_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['protein_g']),
  carbsG: json['carbs_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['carbs_g']),
  fatG: json['fat_g'] == null ? 0 : const JsonDouble().fromJson(json['fat_g']),
  source: json['source'] as String? ?? 'search',
  loggedAt: json['logged_at'] == null
      ? null
      : DateTime.parse(json['logged_at'] as String),
);

Map<String, dynamic> _$FoodLogToJson(_FoodLog instance) => <String, dynamic>{
  'id': instance.id,
  'log_date': instance.logDate,
  'slot': instance.slot,
  'food_id': instance.foodId,
  'recipe_id': instance.recipeId,
  'name': instance.name,
  'quantity': const JsonDouble().toJson(instance.quantity),
  'unit': instance.unit,
  'kcal': const JsonDouble().toJson(instance.kcal),
  'protein_g': const JsonDouble().toJson(instance.proteinG),
  'carbs_g': const JsonDouble().toJson(instance.carbsG),
  'fat_g': const JsonDouble().toJson(instance.fatG),
  'source': instance.source,
  'logged_at': instance.loggedAt?.toIso8601String(),
};

_PlannedItem _$PlannedItemFromJson(Map<String, dynamic> json) => _PlannedItem(
  mealItemId: json['meal_item_id'] as String?,
  foodId: json['food_id'] as String?,
  recipeId: json['recipe_id'] as String?,
  name: json['name'] as String,
  quantity: json['quantity'] == null
      ? 0
      : const JsonDouble().fromJson(json['quantity']),
  unit: json['unit'] as String? ?? 'g',
  kcal: json['kcal'] == null ? 0 : const JsonDouble().fromJson(json['kcal']),
  proteinG: json['protein_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['protein_g']),
  carbsG: json['carbs_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['carbs_g']),
  fatG: json['fat_g'] == null ? 0 : const JsonDouble().fromJson(json['fat_g']),
  logged: json['logged'] as bool? ?? false,
);

Map<String, dynamic> _$PlannedItemToJson(_PlannedItem instance) =>
    <String, dynamic>{
      'meal_item_id': instance.mealItemId,
      'food_id': instance.foodId,
      'recipe_id': instance.recipeId,
      'name': instance.name,
      'quantity': const JsonDouble().toJson(instance.quantity),
      'unit': instance.unit,
      'kcal': const JsonDouble().toJson(instance.kcal),
      'protein_g': const JsonDouble().toJson(instance.proteinG),
      'carbs_g': const JsonDouble().toJson(instance.carbsG),
      'fat_g': const JsonDouble().toJson(instance.fatG),
      'logged': instance.logged,
    };

_MealSlot _$MealSlotFromJson(Map<String, dynamic> json) => _MealSlot(
  slot: json['slot'] as String,
  label: json['label'] as String,
  consumed: json['consumed'] == null
      ? const Macros()
      : Macros.fromJson(json['consumed'] as Map<String, dynamic>),
  logs:
      (json['logs'] as List<dynamic>?)
          ?.map((e) => FoodLog.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <FoodLog>[],
  planned:
      (json['planned'] as List<dynamic>?)
          ?.map((e) => PlannedItem.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <PlannedItem>[],
);

Map<String, dynamic> _$MealSlotToJson(_MealSlot instance) => <String, dynamic>{
  'slot': instance.slot,
  'label': instance.label,
  'consumed': instance.consumed.toJson(),
  'logs': instance.logs.map((e) => e.toJson()).toList(),
  'planned': instance.planned.map((e) => e.toJson()).toList(),
};

_Fasting _$FastingFromJson(Map<String, dynamic> json) => _Fasting(
  mode: json['mode'] as String? ?? 'none',
  windowStart: json['window_start'] as String?,
  windowEnd: json['window_end'] as String?,
);

Map<String, dynamic> _$FastingToJson(_Fasting instance) => <String, dynamic>{
  'mode': instance.mode,
  'window_start': instance.windowStart,
  'window_end': instance.windowEnd,
};

_NutritionDay _$NutritionDayFromJson(Map<String, dynamic> json) =>
    _NutritionDay(
      date: json['date'] as String,
      targets: json['targets'] == null
          ? null
          : Macros.fromJson(json['targets'] as Map<String, dynamic>),
      waterMl: json['water_ml'] == null
          ? 0
          : const JsonDouble().fromJson(json['water_ml']),
      waterTargetMl: json['water_target_ml'] == null
          ? 0
          : const JsonDouble().fromJson(json['water_target_ml']),
      consumed: json['consumed'] == null
          ? const Macros()
          : Macros.fromJson(json['consumed'] as Map<String, dynamic>),
      meals:
          (json['meals'] as List<dynamic>?)
              ?.map((e) => MealSlot.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <MealSlot>[],
      fasting: json['fasting'] == null
          ? null
          : Fasting.fromJson(json['fasting'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$NutritionDayToJson(_NutritionDay instance) =>
    <String, dynamic>{
      'date': instance.date,
      'targets': instance.targets?.toJson(),
      'water_ml': const JsonDouble().toJson(instance.waterMl),
      'water_target_ml': const JsonDouble().toJson(instance.waterTargetMl),
      'consumed': instance.consumed.toJson(),
      'meals': instance.meals.map((e) => e.toJson()).toList(),
      'fasting': instance.fasting?.toJson(),
    };

_PlanMeal _$PlanMealFromJson(Map<String, dynamic> json) => _PlanMeal(
  slot: json['slot'] as String,
  name: json['name'] as String?,
  time: json['time'] as String?,
  items:
      (json['items'] as List<dynamic>?)
          ?.map((e) => PlannedItem.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <PlannedItem>[],
);

Map<String, dynamic> _$PlanMealToJson(_PlanMeal instance) => <String, dynamic>{
  'slot': instance.slot,
  'name': instance.name,
  'time': instance.time,
  'items': instance.items.map((e) => e.toJson()).toList(),
};

_PlanDay _$PlanDayFromJson(Map<String, dynamic> json) => _PlanDay(
  dayNumber: (json['day_number'] as num).toInt(),
  label: json['label'] as String?,
  meals:
      (json['meals'] as List<dynamic>?)
          ?.map((e) => PlanMeal.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <PlanMeal>[],
);

Map<String, dynamic> _$PlanDayToJson(_PlanDay instance) => <String, dynamic>{
  'day_number': instance.dayNumber,
  'label': instance.label,
  'meals': instance.meals.map((e) => e.toJson()).toList(),
};

_MealPlan _$MealPlanFromJson(Map<String, dynamic> json) => _MealPlan(
  id: json['id'] as String,
  name: json['name'] as String,
  type: json['type'] as String?,
  days:
      (json['days'] as List<dynamic>?)
          ?.map((e) => PlanDay.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <PlanDay>[],
);

Map<String, dynamic> _$MealPlanToJson(_MealPlan instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'type': instance.type,
  'days': instance.days.map((e) => e.toJson()).toList(),
};

_PhotoCandidate _$PhotoCandidateFromJson(
  Map<String, dynamic> json,
) => _PhotoCandidate(
  food: json['food'] == null
      ? null
      : Food.fromJson(json['food'] as Map<String, dynamic>),
  name: json['name'] as String,
  grams: json['grams'] == null ? 0 : const JsonDouble().fromJson(json['grams']),
  confidence: json['confidence'] == null
      ? 0
      : const JsonDouble().fromJson(json['confidence']),
  kcal: json['kcal'] == null ? 0 : const JsonDouble().fromJson(json['kcal']),
  proteinG: json['protein_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['protein_g']),
  carbsG: json['carbs_g'] == null
      ? 0
      : const JsonDouble().fromJson(json['carbs_g']),
  fatG: json['fat_g'] == null ? 0 : const JsonDouble().fromJson(json['fat_g']),
  lowConfidence: json['low_confidence'] as bool? ?? false,
);

Map<String, dynamic> _$PhotoCandidateToJson(_PhotoCandidate instance) =>
    <String, dynamic>{
      'food': instance.food?.toJson(),
      'name': instance.name,
      'grams': const JsonDouble().toJson(instance.grams),
      'confidence': const JsonDouble().toJson(instance.confidence),
      'kcal': const JsonDouble().toJson(instance.kcal),
      'protein_g': const JsonDouble().toJson(instance.proteinG),
      'carbs_g': const JsonDouble().toJson(instance.carbsG),
      'fat_g': const JsonDouble().toJson(instance.fatG),
      'low_confidence': instance.lowConfidence,
    };

_PhotoRecognition _$PhotoRecognitionFromJson(Map<String, dynamic> json) =>
    _PhotoRecognition(
      candidates:
          (json['candidates'] as List<dynamic>?)
              ?.map((e) => PhotoCandidate.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <PhotoCandidate>[],
      aiOutputId: json['ai_output_id'] as String?,
    );

Map<String, dynamic> _$PhotoRecognitionToJson(_PhotoRecognition instance) =>
    <String, dynamic>{
      'candidates': instance.candidates.map((e) => e.toJson()).toList(),
      'ai_output_id': instance.aiOutputId,
    };
