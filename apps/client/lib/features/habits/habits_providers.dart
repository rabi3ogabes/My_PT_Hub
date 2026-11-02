import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Habits for a day with optimistic one-tap completion (queued offline).
class HabitsController extends AsyncNotifier<Fetched<List<HabitItem>>> {
  HabitsController(this.date);

  final DateTime date;

  @override
  Future<Fetched<List<HabitItem>>> build() => ref.read(habitRepositoryProvider).habits(date);

  Future<void> toggle(HabitItem item) async {
    final completed = !item.completed;
    _replace(item.copyWith(
      completed: completed,
      value: item.isBoolean ? (completed ? 1 : 0) : (completed ? item.target ?? item.value : item.value),
      streak: Streak(
        current: completed ? item.streak.current + 1 : (item.streak.current - 1).clamp(0, 1 << 30),
        best: completed && item.streak.current + 1 > item.streak.best ? item.streak.current + 1 : item.streak.best,
      ),
    ));
    AuHaptics.medium();
    await ref.read(habitRepositoryProvider).log(item.assignmentId, date, completed: completed, value: item.isBoolean ? null : item.target);
    if (completed) unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.habitCompleted()));
  }

  /// Count/numeric habits: add [delta] towards the target.
  Future<void> increment(HabitItem item, double delta) async {
    final value = ((item.value ?? 0) + delta).clamp(0, 1e9).toDouble();
    final completed = item.target != null && value >= item.target!;
    _replace(item.copyWith(value: value, completed: completed));
    AuHaptics.selection();
    await ref.read(habitRepositoryProvider).log(item.assignmentId, date, value: value, completed: completed);
  }

  void _replace(HabitItem next) {
    final current = state.value;
    if (current == null) return;
    state = AsyncData(Fetched(
      [for (final h in current.value) h.assignmentId == next.assignmentId ? next : h],
      fromCache: current.fromCache,
    ));
  }
}

final habitsProvider =
    AsyncNotifierProvider.autoDispose.family<HabitsController, Fetched<List<HabitItem>>, DateTime>(HabitsController.new);

DateTime dateOnly(DateTime d) => DateTime(d.year, d.month, d.day);
