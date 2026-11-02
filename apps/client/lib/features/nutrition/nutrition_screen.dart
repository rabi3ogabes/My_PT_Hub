import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'nutrition_providers.dart';
import 'quick_log_sheet.dart';

String slotLabel(BuildContext context, String slot, {String? fallback}) => switch (slot) {
      'breakfast' => context.l10n.slotBreakfast,
      'lunch' => context.l10n.slotLunch,
      'dinner' => context.l10n.slotDinner,
      'snack' || 'snacks' => context.l10n.slotSnack,
      'suhoor' => context.l10n.slotSuhoor,
      'iftar' => context.l10n.slotIftar,
      'pre_workout' => context.l10n.slotPreWorkout,
      'post_workout' => context.l10n.slotPostWorkout,
      _ => fallback ?? slot,
    };

class NutritionScreen extends ConsumerWidget {
  const NutritionScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final date = ref.watch(nutritionDateProvider);
    final day = ref.watch(nutritionDayProvider(date));
    return AuPage(
      title: l10n.navNutrition,
      onRefresh: () async {
        ref.invalidate(recentFoodsProvider);
        await ref.refresh(nutritionDayProvider(date).future);
      },
      floatingActionButton: FloatingActionButton.extended(
        heroTag: 'log-food',
        backgroundColor: context.au.accent,
        foregroundColor: context.au.onAccent,
        onPressed: () => showQuickLogSheet(context, ref, date: date),
        icon: const Icon(Icons.add_rounded),
        label: Text(l10n.logFood),
      ),
      children: [
        DayStrip(selected: date, onSelected: ref.read(nutritionDateProvider.notifier).select),
        const SizedBox(height: AuSpacing.s5),
        day.when(
          loading: () => const PageSkeleton(),
          error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(nutritionDayProvider(date))),
          data: (fetched) => _DayView(day: fetched.value, date: date, fromCache: fetched.fromCache),
        ),
      ],
    );
  }
}

class _DayView extends ConsumerWidget {
  const _DayView({required this.day, required this.date, required this.fromCache});

  final NutritionDay day;
  final DateTime date;
  final bool fromCache;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final l10n = context.l10n;
    final core = context.core;
    final t = day.targets;
    final remaining = t == null ? null : (t.kcal - day.consumed.kcal);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        CachedNotice(visible: fromCache),
        AuCard(
          variant: AuCardVariant.hero,
          padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
          child: Column(
            children: [
              Row(
                children: [
                  AuProgressRing(
                    progress: t == null || t.kcal <= 0 ? 0 : day.consumed.kcal / t.kcal,
                    size: 140,
                    strokeWidth: 12,
                    semanticLabel: core.calories,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(context.formatNumber(remaining == null ? day.consumed.kcal : remaining.abs(), maxDecimals: 0),
                            style: au.text.numeric.copyWith(fontSize: 28)),
                        Text(
                          remaining == null ? core.unitKcal : (remaining >= 0 ? l10n.kcalLeft : l10n.kcalOver),
                          style: au.text.caption.copyWith(color: remaining != null && remaining < 0 ? au.danger : au.textMuted),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: AuSpacing.s5),
                  Expanded(
                    child: Column(
                      children: [
                        _Macro(label: core.protein, value: day.consumed.proteinG, target: t?.proteinG, color: au.protein),
                        const SizedBox(height: AuSpacing.s3),
                        _Macro(label: core.carbs, value: day.consumed.carbsG, target: t?.carbsG, color: au.carbs),
                        const SizedBox(height: AuSpacing.s3),
                        _Macro(label: core.fat, value: day.consumed.fatG, target: t?.fatG, color: au.fat),
                      ],
                    ),
                  ),
                ],
              ),
              if (t != null) ...[
                const SizedBox(height: AuSpacing.s4),
                Text(
                  l10n.kcalOfTarget(context.formatNumber(day.consumed.kcal, maxDecimals: 0), context.formatNumber(t.kcal, maxDecimals: 0)),
                  style: au.text.caption.copyWith(color: au.textSecondary),
                ),
              ],
            ],
          ),
        ),
        const SizedBox(height: AuSpacing.s4),
        if (day.fasting != null && day.fasting!.mode != 'none' && day.fasting!.windowStart != null && day.fasting!.windowEnd != null) ...[
          FastingCard(fasting: day.fasting!),
          const SizedBox(height: AuSpacing.s4),
        ],
        WaterCard(day: day, date: date),
        const SizedBox(height: AuSpacing.s6),
        if (day.meals.isEmpty)
          AuEmptyState(
            icon: Icons.restaurant_menu_rounded,
            title: l10n.noMealsTitle,
            message: l10n.noMealsHint,
            actionLabel: l10n.logFood,
            onAction: () => showQuickLogSheet(context, ref, date: date),
          )
        else
          for (final meal in day.meals) ...[
            _MealCard(meal: meal, date: date),
            const SizedBox(height: AuSpacing.s3),
          ],
        const SizedBox(height: AuSpacing.s3),
        AuButton.ghost(
          label: l10n.copyYesterday,
          icon: Icons.content_copy_rounded,
          expand: true,
          onPressed: () async {
            try {
              await ref.read(nutritionDayProvider(date).notifier).copyFromYesterday();
            } on ApiFailure catch (f) {
              if (context.mounted) showError(context, f);
            }
          },
        ),
      ],
    );
  }
}

class _Macro extends StatelessWidget {
  const _Macro({required this.label, required this.value, required this.target, required this.color});

  final String label;
  final double value;
  final double? target;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final g = context.core.unitG;
    final v = context.formatNumber(value, maxDecimals: 0);
    return AuMacroBar(
      label: label,
      value: value,
      target: target,
      color: color,
      valueLabel: target == null ? '$v $g' : '$v/${context.formatNumber(target!, maxDecimals: 0)} $g',
    );
  }
}

class _MealCard extends ConsumerWidget {
  const _MealCard({required this.meal, required this.date});

  final MealSlot meal;
  final DateTime date;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final l10n = context.l10n;
    final controller = ref.read(nutritionDayProvider(date).notifier);
    final unlogged = meal.planned.where((p) => !p.logged).toList();
    return AuCard(
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s4, AuSpacing.s4, AuSpacing.s2, AuSpacing.s2),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(slotLabel(context, meal.slot, fallback: meal.label), style: au.text.title),
                      Text(
                        '${context.formatNumber(meal.consumed.kcal, maxDecimals: 0)} ${context.core.unitKcal}',
                        style: au.text.caption.copyWith(color: au.textMuted),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  tooltip: l10n.addToMeal,
                  icon: Icon(Icons.add_circle_outline_rounded, color: au.accent),
                  onPressed: () => showQuickLogSheet(context, ref, date: date, slot: meal.slot),
                ),
              ],
            ),
          ),
          for (final log in meal.logs)
            Dismissible(
              key: ValueKey(log.id),
              direction: DismissDirection.endToStart,
              background: Container(
                color: au.dangerSoft,
                alignment: AlignmentDirectional.centerEnd,
                padding: const EdgeInsetsDirectional.only(end: AuSpacing.s5),
                child: Icon(Icons.delete_outline_rounded, color: au.danger),
              ),
              onDismissed: (_) => controller.delete(log),
              child: AuListRow(
                dense: true,
                title: log.name,
                subtitle: '${context.formatNumber(log.quantity)} ${log.unit} · ${context.formatNumber(log.kcal, maxDecimals: 0)} ${context.core.unitKcal}',
                trailing: Text(
                  'P${context.formatNumber(log.proteinG, maxDecimals: 0)} C${context.formatNumber(log.carbsG, maxDecimals: 0)} F${context.formatNumber(log.fatG, maxDecimals: 0)}',
                  style: au.text.caption.copyWith(color: au.textMuted),
                ),
              ),
            ),
          if (unlogged.isNotEmpty) ...[
            const Divider(),
            Padding(
              padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s4, AuSpacing.s2, AuSpacing.s4, 0),
              child: Text(l10n.plannedByCoach.toUpperCase(), style: au.text.overline.copyWith(color: au.textMuted)),
            ),
            for (final p in unlogged)
              AuListRow(
                dense: true,
                leading: Icon(Icons.radio_button_unchecked_rounded, size: 18, color: au.borderStrong),
                title: p.name,
                subtitle: '${context.formatNumber(p.quantity)} ${p.unit} · ${context.formatNumber(p.kcal, maxDecimals: 0)} ${context.core.unitKcal}',
                onTap: () => controller.log(
                  slot: meal.slot,
                  name: p.name,
                  quantity: p.quantity,
                  unit: p.unit,
                  macros: p.macros,
                  source: 'plan',
                  foodId: p.foodId,
                  recipeId: p.recipeId,
                ),
              ),
            Padding(
              padding: const EdgeInsetsDirectional.all(AuSpacing.s3),
              child: AuButton.secondary(
                label: l10n.logFromPlan,
                icon: Icons.playlist_add_check_rounded,
                size: AuButtonSize.compact,
                onPressed: () async {
                  try {
                    await controller.logFromPlan(meal);
                  } on ApiFailure catch (f) {
                    if (context.mounted) showError(context, f);
                  }
                },
              ),
            ),
          ] else
            const SizedBox(height: AuSpacing.s2),
        ],
      ),
    );
  }
}

/// Water tracker with quick-add glasses.
class WaterCard extends ConsumerWidget {
  const WaterCard({super.key, required this.day, required this.date});

  final NutritionDay day;
  final DateTime date;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final units = ref.watch(unitsProvider);
    final controller = ref.read(nutritionDayProvider(date).notifier);
    final ratio = day.waterTargetMl <= 0 ? 0.0 : (day.waterMl / day.waterTargetMl).clamp(0.0, 1.0);
    final glass = units == UnitSystem.imperial ? 236.6 : 250.0;
    return AuCard(
      child: Row(
        children: [
          SizedBox(
            width: 44,
            height: 64,
            child: CustomPaint(painter: _GlassPainter(fill: ratio, color: au.info, outline: au.borderStrong)),
          ),
          const SizedBox(width: AuSpacing.s4),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(context.l10n.water, style: au.text.title),
                Text(
                  '${Units.formatWater(day.waterMl, units, locale: context.localeName)} / ${Units.formatWater(day.waterTargetMl, units, locale: context.localeName)}',
                  style: au.text.caption.copyWith(color: au.textSecondary),
                ),
              ],
            ),
          ),
          AuIconButton(
            icon: Icons.remove_rounded,
            tooltip: context.core.decrease,
            filled: true,
            onPressed: day.waterMl <= 0 ? null : () => controller.addWater(-glass),
          ),
          const SizedBox(width: AuSpacing.s2),
          Semantics(
            button: true,
            label: context.l10n.addGlass,
            child: GestureDetector(
              onTap: () => controller.addWater(glass),
              child: Container(
                height: 44,
                padding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s4),
                decoration: BoxDecoration(color: au.infoSoft, borderRadius: BorderRadius.circular(AuRadius.pill), border: Border.all(color: au.info.withValues(alpha: 0.5))),
                alignment: Alignment.center,
                child: Text('+ ${Units.formatWater(glass, units, locale: context.localeName)}', style: au.text.label.copyWith(color: au.info)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _GlassPainter extends CustomPainter {
  _GlassPainter({required this.fill, required this.color, required this.outline});

  final double fill;
  final Color color;
  final Color outline;

  @override
  void paint(Canvas canvas, Size size) {
    final path = Path()
      ..moveTo(0, 0)
      ..lineTo(size.width, 0)
      ..lineTo(size.width * 0.85, size.height)
      ..lineTo(size.width * 0.15, size.height)
      ..close();
    canvas.save();
    canvas.clipPath(path);
    canvas.drawRect(Rect.fromLTWH(0, size.height * (1 - fill), size.width, size.height * fill), Paint()..color = color.withValues(alpha: 0.75));
    canvas.restore();
    canvas.drawPath(path, Paint()
      ..color = outline
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2
      ..strokeJoin = StrokeJoin.round);
  }

  @override
  bool shouldRepaint(_GlassPainter old) => old.fill != fill || old.color != color;
}

/// Intermittent fasting / Ramadan timer.
class FastingCard extends StatefulWidget {
  const FastingCard({super.key, required this.fasting});

  final Fasting fasting;

  @override
  State<FastingCard> createState() => _FastingCardState();
}

class _FastingCardState extends State<FastingCard> {
  late final Timer _timer = Timer.periodic(const Duration(seconds: 30), (_) {
    if (mounted) setState(() {});
  });

  @override
  void dispose() {
    _timer.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final f = widget.fasting;
    final now = DateTime.now();
    final status = fastingStatus(now, f.windowStart!, f.windowEnd!);
    final left = status.nextChange.difference(now);
    final ramadan = f.mode == 'ramadan';
    final hours = left.inHours;
    final minutes = left.inMinutes.remainder(60);
    return AuCard(
      variant: AuCardVariant.hero,
      child: Row(
        children: [
          AuIconTile(icon: ramadan ? Icons.nightlight_round : Icons.hourglass_bottom_rounded, color: status.fasting ? au.accent : au.success),
          const SizedBox(width: AuSpacing.s4),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  (ramadan ? l10n.ramadanMode : l10n.intermittentFasting).toUpperCase(),
                  style: au.text.overline.copyWith(color: au.accent),
                ),
                Text(status.fasting ? l10n.fastingNow : l10n.eatingWindow, style: au.text.title),
                Text(
                  status.fasting
                      ? (ramadan ? l10n.iftarIn(hours, minutes) : l10n.windowOpensIn(hours, minutes))
                      : (ramadan ? l10n.suhoorEndsIn(hours, minutes) : l10n.windowClosesIn(hours, minutes)),
                  style: au.text.caption.copyWith(color: au.textSecondary),
                ),
              ],
            ),
          ),
          Directionality(
            textDirection: TextDirection.ltr,
            child: Text('${f.windowStart} – ${f.windowEnd}', style: au.text.label.copyWith(color: au.textMuted)),
          ),
        ],
      ),
    );
  }
}
