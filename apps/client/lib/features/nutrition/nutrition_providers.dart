import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Selected day on the Nutrition tab.
class NutritionDateController extends Notifier<DateTime> {
  @override
  DateTime build() {
    final now = DateTime.now();
    return DateTime(now.year, now.month, now.day);
  }

  void select(DateTime date) => state = DateTime(date.year, date.month, date.day);
}

final nutritionDateProvider = NotifierProvider<NutritionDateController, DateTime>(NutritionDateController.new);

/// A day of nutrition with optimistic, offline-first logging.
class NutritionDayController extends AsyncNotifier<Fetched<NutritionDay>> {
  NutritionDayController(this.date);

  final DateTime date;

  NutritionRepository get _repo => ref.read(nutritionRepositoryProvider);

  @override
  Future<Fetched<NutritionDay>> build() => _repo.day(date);

  NutritionDay? get _day => state.value?.value;

  void _set(NutritionDay day) => state = AsyncData(Fetched(day, fromCache: state.value?.fromCache ?? false));

  /// Logs a food and updates totals immediately.
  Future<FoodLog> log({
    required String slot,
    required String name,
    required double quantity,
    required String unit,
    required Macros macros,
    required String source,
    String? foodId,
    String? recipeId,
  }) async {
    final log = await _repo.logFood(
      date: date,
      slot: slot,
      name: name,
      quantity: quantity,
      unit: unit,
      macros: macros,
      source: source,
      foodId: foodId,
      recipeId: recipeId,
    );
    final day = _day;
    if (day != null) _set(applyLog(day, log));
    AuHaptics.medium();
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.foodLogged(source: source, slot: slot)));
    return log;
  }

  Future<void> delete(FoodLog log) async {
    await _repo.deleteFoodLog(log);
    final day = _day;
    if (day != null) _set(removeLog(day, log));
  }

  /// One tap per meal. Uses the server endpoint when online; offline, each
  /// planned item is queued as its own log.
  Future<void> logFromPlan(MealSlot meal) async {
    try {
      await _repo.logFromPlan(date, meal.slot);
      state = AsyncData(await _repo.day(date));
    } on ApiFailure catch (f) {
      if (!f.isConnectivity) rethrow;
      for (final item in meal.planned.where((p) => !p.logged)) {
        final log = await _repo.logFood(
          date: date,
          slot: meal.slot,
          name: item.name,
          quantity: item.quantity,
          unit: item.unit,
          macros: item.macros,
          source: 'plan',
          foodId: item.foodId,
          recipeId: item.recipeId,
        );
        final day = _day;
        if (day != null) _set(applyLog(day, log, markPlanned: item));
      }
    }
    AuHaptics.success();
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.foodLogged(source: 'plan', slot: meal.slot)));
  }

  Future<void> addWater(double ml) async {
    await _repo.logWater(date, ml);
    final day = _day;
    if (day != null) _set(day.copyWith(waterMl: (day.waterMl + ml).clamp(0, 20000).toDouble()));
    AuHaptics.selection();
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.waterLogged(ml: ml.round())));
  }

  Future<void> copyFromYesterday({String? slot}) async {
    await _repo.copyDay(from: date.subtract(const Duration(days: 1)), to: date, slot: slot);
    state = AsyncData(await _repo.day(date));
  }

  /// Pure: adds [log] to the day (totals + meal list).
  static NutritionDay applyLog(NutritionDay day, FoodLog log, {PlannedItem? markPlanned}) {
    final meals = [...day.meals];
    final index = meals.indexWhere((m) => m.slot == log.slot);
    if (index >= 0) {
      final meal = meals[index];
      meals[index] = meal.copyWith(
        logs: [...meal.logs, log],
        consumed: meal.consumed + log.macros,
        planned: markPlanned == null
            ? meal.planned
            : [for (final p in meal.planned) identical(p, markPlanned) ? p.copyWith(logged: true) : p],
      );
    } else {
      meals.add(MealSlot(slot: log.slot, label: log.slot, consumed: log.macros, logs: [log]));
    }
    return day.copyWith(meals: meals, consumed: day.consumed + log.macros);
  }

  static NutritionDay removeLog(NutritionDay day, FoodLog log) {
    final negative = log.macros.scale(-1);
    return day.copyWith(
      consumed: day.consumed + negative,
      meals: [
        for (final m in day.meals)
          m.slot == log.slot ? m.copyWith(logs: m.logs.where((l) => l.id != log.id).toList(), consumed: m.consumed + negative) : m,
      ],
    );
  }
}

final nutritionDayProvider =
    AsyncNotifierProvider.autoDispose.family<NutritionDayController, Fetched<NutritionDay>, DateTime>(NutritionDayController.new);

final recentFoodsProvider = FutureProvider.autoDispose<Fetched<List<Food>>>((ref) => ref.watch(nutritionRepositoryProvider).recent());

/// Fasting state for a window like iftar 18:05 → suhoor 04:12 (eating window
/// may cross midnight). Returns whether the user is fasting at [now] and when
/// the state next changes.
({bool fasting, DateTime nextChange}) fastingStatus(DateTime now, String windowStart, String windowEnd) {
  DateTime at(String hm, DateTime day) {
    final parts = hm.split(':').map(int.parse).toList();
    return DateTime(day.year, day.month, day.day, parts[0], parts[1]);
  }

  final today = DateTime(now.year, now.month, now.day);
  final start = at(windowStart, today);
  var end = at(windowEnd, today);
  final crossesMidnight = !end.isAfter(start);
  if (crossesMidnight) {
    // Eating from start (evening) until end (next morning).
    final endToday = end;
    if (now.isBefore(endToday)) return (fasting: false, nextChange: endToday);
    if (now.isBefore(start)) return (fasting: true, nextChange: start);
    end = endToday.add(const Duration(days: 1));
    return (fasting: false, nextChange: end);
  }
  if (now.isBefore(start)) return (fasting: true, nextChange: start);
  if (now.isBefore(end)) return (fasting: false, nextChange: end);
  return (fasting: true, nextChange: start.add(const Duration(days: 1)));
}
