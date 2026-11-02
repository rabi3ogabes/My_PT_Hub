import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import '../train/exercise_progress_screen.dart';
import '../train/records_screen.dart';

final progressProvider = FutureProvider.autoDispose<Fetched<ProgressSummary>>(
  (ref) => ref.watch(progressRepositoryProvider).progress(),
);

final checkinsProvider = FutureProvider.autoDispose<Fetched<List<CheckinSummary>>>(
  (ref) => ref.watch(progressRepositoryProvider).checkins(),
);

const measurementKeys = ['neck', 'chest', 'waist', 'hips', 'arms', 'thighs', 'calves'];

String measurementLabel(BuildContext context, String key) => switch (key) {
      'neck' => context.l10n.mNeck,
      'chest' => context.l10n.mChest,
      'waist' => context.l10n.mWaist,
      'hips' => context.l10n.mHips,
      'arms' => context.l10n.mArms,
      'thighs' => context.l10n.mThighs,
      'calves' => context.l10n.mCalves,
      _ => key,
    };

class ProgressScreen extends ConsumerWidget {
  const ProgressScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final progress = ref.watch(progressProvider);
    final checkins = ref.watch(checkinsProvider);
    return AuPage(
      title: l10n.navProgress,
      onRefresh: () async {
        ref.invalidate(checkinsProvider);
        await ref.refresh(progressProvider.future);
      },
      actions: [
        IconButton(tooltip: l10n.logWeight, icon: const Icon(Icons.monitor_weight_outlined), onPressed: () => showLogMetricsSheet(context, ref)),
      ],
      children: [
        progress.when(
          loading: () => const PageSkeleton(),
          error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(progressProvider)),
          data: (fetched) => _ProgressContent(summary: fetched.value, fromCache: fetched.fromCache),
        ),
        const SizedBox(height: AuSpacing.s6),
        AuSectionHeader(title: l10n.checkIns),
        AsyncSection(
          value: checkins,
          skeletonHeight: 80,
          onRetry: () => ref.invalidate(checkinsProvider),
          builder: (fetched) => fetched.value.isEmpty
              ? AuCard(variant: AuCardVariant.sunken, child: Text(l10n.noCheckinsHint, style: context.au.text.bodySmall.copyWith(color: context.au.textSecondary)))
              : Column(
                  children: [
                    for (final c in fetched.value.take(6)) ...[
                      _CheckinRow(checkin: c),
                      const SizedBox(height: AuSpacing.s2),
                    ],
                  ],
                ),
        ),
      ],
    );
  }
}

class _CheckinRow extends StatelessWidget {
  const _CheckinRow({required this.checkin});

  final CheckinSummary checkin;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final (label, tone) = switch (checkin.status) {
      'submitted' => (l10n.statusSubmitted, AuBadgeTone.accent),
      'reviewed' => (l10n.statusReviewed, AuBadgeTone.success),
      'overdue' => (l10n.statusOverdue, AuBadgeTone.danger),
      _ => (l10n.statusPending, AuBadgeTone.warning),
    };
    final actionable = checkin.status == 'pending' || checkin.status == 'overdue';
    return AuCard(
      onTap: actionable ? () => context.push(Routes.checkin(checkin.id)) : null,
      child: Row(
        children: [
          const AuIconTile(icon: Icons.assignment_outlined),
          const SizedBox(width: AuSpacing.s3),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(checkin.formName ?? l10n.checkIn, style: au.text.title),
                Text(context.formatDate(parseApiDate(checkin.dueOn), pattern: 'yMMMd'), style: au.text.caption.copyWith(color: au.textMuted)),
              ],
            ),
          ),
          AuBadge(label: label, tone: tone, solid: false),
        ],
      ),
    );
  }
}

class _ProgressContent extends ConsumerWidget {
  const _ProgressContent({required this.summary, required this.fromCache});

  final ProgressSummary summary;
  final bool fromCache;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final l10n = context.l10n;
    final units = ref.watch(unitsProvider);
    final weights = [...summary.weight]..sort((a, b) => a.date.compareTo(b.date));
    final latest = weights.lastOrNull;
    final first = weights.firstOrNull;
    final change = latest != null && first != null ? latest.weightKg - first.weightKg : null;
    final c = summary.compliance;
    final latestMeasurements = summary.measurements.isEmpty
        ? null
        : (summary.measurements.toList()..sort((a, b) => a.date.compareTo(b.date))).last;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        CachedNotice(visible: fromCache),
        Row(
          children: [
            Expanded(
              child: AuStatTile(
                label: context.core.weight,
                value: latest == null ? '—' : context.formatNumber(Units.weightToDisplay(latest.weightKg, units)),
                unit: weightUnitLabel(context, units),
                icon: Icons.monitor_weight_outlined,
                trend: change == null || change == 0 ? null : '${change > 0 ? '+' : ''}${context.formatNumber(Units.weightToDisplay(change, units))}',
                trendPositive: (change ?? 0) <= 0,
                onTap: () => showLogMetricsSheet(context, ref),
              ),
            ),
            const SizedBox(width: AuSpacing.s3),
            Expanded(
              child: AuStatTile(label: l10n.compliance, value: '${c.score.round()}', unit: '%', icon: Icons.verified_outlined),
            ),
          ],
        ),
        const SizedBox(height: AuSpacing.s5),
        AuSectionHeader(title: l10n.weightTrend, actionLabel: l10n.logWeight, onAction: () => showLogMetricsSheet(context, ref)),
        if (weights.length < 2)
          AuEmptyState(
            icon: Icons.show_chart_rounded,
            title: l10n.notEnoughData,
            message: l10n.weighInHint,
            actionLabel: l10n.logWeight,
            onAction: () => showLogMetricsSheet(context, ref),
            compact: true,
          )
        else
          AuCard(
            padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s2, AuSpacing.s5, AuSpacing.s5, AuSpacing.s3),
            child: SizedBox(
              height: 200,
              child: TrendChart(
                values: [for (final w in weights) Units.weightToDisplay(w.weightKg, units)],
                labels: [for (final w in weights) context.formatDate(parseApiDate(w.date), pattern: 'd/M')],
              ),
            ),
          ),
        const SizedBox(height: AuSpacing.s6),
        AuSectionHeader(title: l10n.complianceBreakdown),
        AuCard(
          child: Column(
            children: [
              AuMacroBar(label: l10n.navTrain, value: c.workoutsPct, target: 100, color: au.accent, valueLabel: '${c.workoutsPct.round()}%'),
              const SizedBox(height: AuSpacing.s3),
              AuMacroBar(label: l10n.navNutrition, value: c.nutritionPct, target: 100, color: au.kcal, valueLabel: '${c.nutritionPct.round()}%'),
              const SizedBox(height: AuSpacing.s3),
              AuMacroBar(label: l10n.habits, value: c.habitsPct, target: 100, color: au.fat, valueLabel: '${c.habitsPct.round()}%'),
              const SizedBox(height: AuSpacing.s3),
              AuMacroBar(label: l10n.checkIns, value: c.checkinsPct, target: 100, color: au.protein, valueLabel: '${c.checkinsPct.round()}%'),
            ],
          ),
        ),
        const SizedBox(height: AuSpacing.s6),
        AuSectionHeader(
          title: l10n.progressPhotos,
          actionLabel: summary.photos.length >= 2 ? l10n.compare : null,
          onAction: () => context.push('${Routes.progress}/compare'),
        ),
        if (summary.photos.isEmpty)
          AuCard(variant: AuCardVariant.sunken, child: Text(l10n.noPhotosHint, style: au.text.bodySmall.copyWith(color: au.textSecondary)))
        else
          SizedBox(
            height: 150,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: summary.photos.length,
              separatorBuilder: (_, _) => const SizedBox(width: AuSpacing.s2),
              itemBuilder: (_, i) {
                final p = summary.photos[i];
                return ClipRRect(
                  borderRadius: BorderRadius.circular(AuRadius.md),
                  child: Stack(
                    children: [
                      Container(
                        width: 110,
                        color: au.surfaceSunken,
                        child: p.url == null ? null : Image.network(p.url!, fit: BoxFit.cover, width: 110, height: 150, errorBuilder: (_, _, _) => const SizedBox()),
                      ),
                      PositionedDirectional(
                        start: 6,
                        bottom: 6,
                        child: AuBadge(label: context.formatDate(parseApiDate(p.takenOn), pattern: 'd MMM'), tone: AuBadgeTone.neutral),
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
        if (latestMeasurements != null) ...[
          const SizedBox(height: AuSpacing.s6),
          AuSectionHeader(title: l10n.measurements),
          AuCard(
            padding: EdgeInsets.zero,
            child: Column(
              children: [
                for (final key in latestMeasurements.values.keys)
                  if (latestMeasurements.valueOf(key) != null)
                    AuListRow(
                      dense: true,
                      title: measurementLabel(context, key),
                      trailing: Text(
                        Units.formatLength(latestMeasurements.valueOf(key)!, units, locale: context.localeName),
                        style: au.text.label,
                      ),
                    ),
              ],
            ),
          ),
        ],
        if (summary.records.isNotEmpty) ...[
          const SizedBox(height: AuSpacing.s6),
          AuSectionHeader(title: l10n.personalRecords, actionLabel: context.core.seeAll, onAction: () => context.push('${Routes.train}/records')),
          PrBoard(records: summary.records, limit: 4),
        ],
      ],
    );
  }
}

/// Weight / body-fat / measurements entry (queued offline).
Future<void> showLogMetricsSheet(BuildContext context, WidgetRef ref) => showAuBottomSheet<void>(
      context: context,
      title: context.l10n.logWeight,
      builder: (_) => const _LogMetricsView(),
    );

class _LogMetricsView extends ConsumerStatefulWidget {
  const _LogMetricsView();

  @override
  ConsumerState<_LogMetricsView> createState() => _LogMetricsViewState();
}

class _LogMetricsViewState extends ConsumerState<_LogMetricsView> {
  late UnitSystem _units = ref.read(unitsProvider);
  double _weight = 75;
  double? _bodyFat;
  final Map<String, double> _measurements = {};
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final weights = ref.read(progressProvider).value?.value.weight ?? const [];
    if (weights.isNotEmpty) {
      final last = ([...weights]..sort((a, b) => a.date.compareTo(b.date))).last.weightKg;
      _weight = double.parse(Units.weightToDisplay(last, _units).toStringAsFixed(1));
    } else {
      _weight = _units == UnitSystem.imperial ? 165 : 75;
    }
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    await ref.read(progressRepositoryProvider).logBodyMetric(
          measuredOn: DateTime.now(),
          weightKg: Units.weightFromDisplay(_weight, _units),
          bodyFatPct: _bodyFat,
          measurements: _measurements.map((k, v) => MapEntry(k, Units.lengthFromDisplay(v, _units))),
        );
    ref.invalidate(progressProvider);
    if (mounted) {
      AuToast.show(context, context.l10n.savedWillSync, tone: AuToastTone.success);
      Navigator.of(context).pop();
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return ListView(
      shrinkWrap: true,
      padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s2, AuSpacing.s5, AuSpacing.s6),
      children: [
        AuSegmented<UnitSystem>(
          segments: {UnitSystem.metric: context.core.unitsMetric, UnitSystem.imperial: context.core.unitsImperial},
          selected: _units,
          onChanged: (u) => setState(() {
            _weight = double.parse(Units.weightToDisplay(Units.weightFromDisplay(_weight, _units), u).toStringAsFixed(1));
            _measurements.updateAll((k, v) => double.parse(Units.lengthToDisplay(Units.lengthFromDisplay(v, _units), u).toStringAsFixed(1)));
            _units = u;
          }),
        ),
        const SizedBox(height: AuSpacing.s5),
        Center(
          child: AuStepper(
            value: _weight,
            step: 0.1,
            min: 20,
            max: 700,
            unit: weightUnitLabel(context, _units),
            label: context.core.weight,
            onChanged: (v) => setState(() => _weight = v),
          ),
        ),
        const SizedBox(height: AuSpacing.s4),
        MeasurementInputs(
          units: _units,
          values: _measurements,
          bodyFat: _bodyFat,
          onBodyFat: (v) => setState(() => _bodyFat = v),
          onChanged: (k, v) => setState(() => v == null ? _measurements.remove(k) : _measurements[k] = v),
        ),
        const SizedBox(height: AuSpacing.s5),
        AuButton(label: context.core.save, loading: _saving, onPressed: _save),
        const SizedBox(height: AuSpacing.s1),
        Text(l10n.metricStorageNote, style: context.au.text.caption.copyWith(color: context.au.textMuted), textAlign: TextAlign.center),
      ],
    );
  }
}

/// Body-fat and circumference inputs in the chosen unit.
class MeasurementInputs extends StatelessWidget {
  const MeasurementInputs({
    super.key,
    required this.units,
    required this.values,
    required this.onChanged,
    this.bodyFat,
    this.onBodyFat,
  });

  final UnitSystem units;
  final Map<String, double> values;
  final void Function(String key, double? value) onChanged;
  final double? bodyFat;
  final ValueChanged<double?>? onBodyFat;

  @override
  Widget build(BuildContext context) {
    final unit = lengthUnitLabel(context, units);
    return Column(
      children: [
        if (onBodyFat != null)
          Padding(
            padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s3),
            child: AuTextField(
              label: '${context.l10n.bodyFat} (%)',
              initialValue: bodyFat?.toString(),
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              textDirection: TextDirection.ltr,
              onChanged: (v) => onBodyFat!(double.tryParse(v.replaceAll(',', '.'))),
            ),
          ),
        Wrap(
          spacing: AuSpacing.s3,
          runSpacing: AuSpacing.s3,
          children: [
            for (final key in measurementKeys)
              SizedBox(
                width: (MediaQuery.sizeOf(context).width - AuSpacing.s5 * 2 - AuSpacing.s3) / 2,
                child: AuTextField(
                  key: ValueKey('$key-${units.name}'),
                  label: '${measurementLabel(context, key)} ($unit)',
                  initialValue: values[key]?.toString(),
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  textDirection: TextDirection.ltr,
                  onChanged: (v) => onChanged(key, double.tryParse(v.replaceAll(',', '.'))),
                ),
              ),
          ],
        ),
      ],
    );
  }
}
