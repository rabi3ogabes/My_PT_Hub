import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'habits_providers.dart';

IconData habitIcon(String? icon) => switch (icon) {
      'water' => Icons.water_drop_outlined,
      'steps' => Icons.directions_walk_rounded,
      'sleep' => Icons.bedtime_outlined,
      'protein' => Icons.egg_alt_outlined,
      'veggies' => Icons.eco_outlined,
      'meditation' => Icons.self_improvement_rounded,
      'no_sugar' => Icons.no_food_outlined,
      'supplements' => Icons.medication_outlined,
      'reading' => Icons.menu_book_outlined,
      _ => Icons.check_circle_outline_rounded,
    };

/// Full habits checklist with day picker.
class HabitsScreen extends ConsumerStatefulWidget {
  const HabitsScreen({super.key});

  @override
  ConsumerState<HabitsScreen> createState() => _HabitsScreenState();
}

class _HabitsScreenState extends ConsumerState<HabitsScreen> {
  DateTime _date = dateOnly(DateTime.now());

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final habits = ref.watch(habitsProvider(_date));
    return AuPage(
      title: l10n.habits,
      onRefresh: () => ref.refresh(habitsProvider(_date).future),
      children: [
        DayStrip(selected: _date, onSelected: (d) => setState(() => _date = dateOnly(d))),
        const SizedBox(height: AuSpacing.s5),
        habits.when(
          loading: () => const PageSkeleton(),
          error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(habitsProvider(_date))),
          data: (fetched) {
            final items = fetched.value.where((h) => h.scheduledToday).toList();
            if (items.isEmpty) {
              return AuEmptyState(icon: Icons.checklist_rounded, title: l10n.noHabitsTitle, message: l10n.noHabitsHint);
            }
            final done = items.where((h) => h.completed).length;
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                CachedNotice(visible: fetched.fromCache),
                AuCard(
                  variant: AuCardVariant.hero,
                  child: Row(
                    children: [
                      AuProgressRing(
                        progress: done / items.length,
                        size: 64,
                        strokeWidth: 7,
                        child: Text('$done/${items.length}', style: context.au.text.label),
                      ),
                      const SizedBox(width: AuSpacing.s4),
                      Expanded(
                        child: Text(
                          done == items.length ? l10n.allHabitsDone : l10n.habitsRemaining(items.length - done),
                          style: context.au.text.title,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: AuSpacing.s4),
                for (final h in items) ...[
                  HabitTile(item: h, date: _date),
                  const SizedBox(height: AuSpacing.s3),
                ],
              ],
            );
          },
        ),
      ],
    );
  }
}

/// Today's first habits (Today screen preview).
class HabitsPreview extends ConsumerWidget {
  const HabitsPreview({super.key, this.limit = 3});

  final int limit;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final date = dateOnly(DateTime.now());
    final habits = ref.watch(habitsProvider(date));
    return AsyncSection(
      value: habits,
      skeletonHeight: 64,
      onRetry: () => ref.invalidate(habitsProvider(date)),
      builder: (fetched) {
        final items = fetched.value.where((h) => h.scheduledToday).take(limit).toList();
        if (items.isEmpty) {
          return AuCard(
            variant: AuCardVariant.sunken,
            child: Text(context.l10n.noHabitsHint, style: context.au.text.bodySmall.copyWith(color: context.au.textSecondary)),
          );
        }
        return Column(
          children: [
            for (final h in items) ...[HabitTile(item: h, date: date, dense: true), const SizedBox(height: AuSpacing.s2)],
          ],
        );
      },
    );
  }
}

/// One habit: icon, name, streak flame, one-tap completion (or +/− for counts).
class HabitTile extends ConsumerWidget {
  const HabitTile({super.key, required this.item, required this.date, this.dense = false});

  final HabitItem item;
  final DateTime date;
  final bool dense;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final controller = ref.read(habitsProvider(date).notifier);
    final countable = !item.isBoolean && item.target != null;
    String valueLabel() {
      final v = context.formatNumber(item.value ?? 0, maxDecimals: 1);
      final t = context.formatNumber(item.target ?? 0, maxDecimals: 1);
      return '$v / $t ${item.habit.unit ?? ''}'.trim();
    }

    return AuCard(
      padding: EdgeInsetsDirectional.all(dense ? AuSpacing.s3 : AuSpacing.s4),
      onTap: countable ? null : () => controller.toggle(item),
      child: Row(
        children: [
          AuIconTile(icon: habitIcon(item.habit.icon), color: item.completed ? au.success : au.accent, size: dense ? 36 : 44),
          const SizedBox(width: AuSpacing.s3),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.habit.name, style: au.text.title.copyWith(fontSize: dense ? 16 : 18)),
                const SizedBox(height: 2),
                Row(
                  children: [
                    AuStreakFlame(count: item.streak.current, size: 16, semanticLabel: context.l10n.streakLabel(item.streak.current)),
                    if (countable) ...[
                      const SizedBox(width: AuSpacing.s3),
                      Text(valueLabel(), style: au.text.caption.copyWith(color: au.textSecondary)),
                    ],
                    if (!dense && !countable) ...[
                      const SizedBox(width: AuSpacing.s3),
                      Text(context.l10n.completionRate(item.completionPct30d.round()), style: au.text.caption.copyWith(color: au.textMuted)),
                    ],
                  ],
                ),
                if (countable) ...[
                  const SizedBox(height: AuSpacing.s2),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(AuRadius.pill),
                    child: LinearProgressIndicator(value: item.progress, minHeight: 5, backgroundColor: au.border, color: item.completed ? au.success : au.accent),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(width: AuSpacing.s2),
          if (countable) ...[
            AuIconButton(
              icon: Icons.remove_rounded,
              tooltip: context.core.decrease,
              filled: true,
              size: 40,
              onPressed: (item.value ?? 0) <= 0 ? null : () => controller.increment(item, -1),
            ),
            const SizedBox(width: AuSpacing.s1),
            AuIconButton(icon: Icons.add_rounded, tooltip: context.core.increase, filled: true, size: 40, onPressed: () => controller.increment(item, 1)),
          ] else
            Semantics(
              checked: item.completed,
              button: true,
              label: item.habit.name,
              excludeSemantics: true,
              child: AnimatedContainer(
                duration: AuMotion.base,
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: item.completed ? au.goldGradient : null,
                  border: item.completed ? null : Border.all(color: au.borderStrong, width: 1.5),
                ),
                child: item.completed ? Icon(Icons.check_rounded, color: au.onAccent, size: 22) : null,
              ),
            ),
        ],
      ),
    );
  }
}
