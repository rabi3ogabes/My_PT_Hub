import 'package:aurum_core/aurum_core.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'train_providers.dart';

/// Exercise progress: estimated 1RM trend, bests and recent top sets.
class ExerciseProgressScreen extends ConsumerWidget {
  const ExerciseProgressScreen({super.key, required this.exerciseId, required this.name});

  final String exerciseId;
  final String name;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final au = context.au;
    final units = ref.watch(unitsProvider);
    final history = ref.watch(exerciseHistoryProvider(exerciseId));
    return AuPage(
      title: name,
      onRefresh: () => ref.refresh(exerciseHistoryProvider(exerciseId).future),
      children: [
        history.when(
          loading: () => const PageSkeleton(),
          error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(exerciseHistoryProvider(exerciseId))),
          data: (fetched) {
            final h = fetched.value;
            final points = h.sessions.where((s) => s.e1rmKg != null).toList()..sort((a, b) => a.date.compareTo(b.date));
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                CachedNotice(visible: fetched.fromCache),
                Row(
                  children: [
                    Expanded(
                      child: AuStatTile(
                        label: l10n.bestE1rm,
                        value: h.bestE1rmKg == null ? '—' : context.formatNumber(Units.weightToDisplay(h.bestE1rmKg!, units)),
                        unit: weightUnitLabel(context, units),
                        icon: Icons.trending_up_rounded,
                      ),
                    ),
                    const SizedBox(width: AuSpacing.s3),
                    Expanded(
                      child: AuStatTile(
                        label: l10n.bestWeight,
                        value: h.bestWeightKg == null ? '—' : context.formatNumber(Units.weightToDisplay(h.bestWeightKg!, units)),
                        unit: weightUnitLabel(context, units),
                        icon: Icons.emoji_events_outlined,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AuSpacing.s5),
                AuSectionHeader(title: l10n.estimatedOneRepMax),
                if (points.length < 2)
                  AuEmptyState(icon: Icons.show_chart_rounded, title: l10n.notEnoughData, message: l10n.notEnoughDataHint, compact: true)
                else
                  AuCard(
                    padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s2, AuSpacing.s5, AuSpacing.s5, AuSpacing.s3),
                    child: SizedBox(
                      height: 220,
                      child: TrendChart(
                        values: [for (final p in points) Units.weightToDisplay(p.e1rmKg!, units)],
                        labels: [for (final p in points) context.formatDate(parseApiDate(p.date), pattern: 'd/M')],
                      ),
                    ),
                  ),
                const SizedBox(height: AuSpacing.s5),
                AuSectionHeader(title: l10n.recentSessions),
                for (final s in h.sessions.reversed.take(12))
                  Padding(
                    padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s2),
                    child: AuCard(
                      child: Row(
                        children: [
                          Expanded(child: Text(context.formatDate(parseApiDate(s.date)), style: au.text.body)),
                          if (s.topSet != null)
                            Text(
                              '${s.topSet!.weightKg == null ? '' : displayWeight(context, s.topSet!.weightKg!, units)} × ${s.topSet!.reps ?? '—'}',
                              style: au.text.label,
                            ),
                        ],
                      ),
                    ),
                  ),
              ],
            );
          },
        ),
      ],
    );
  }
}

/// Elegant single-series line chart (gold line, soft gradient fill).
class TrendChart extends StatelessWidget {
  const TrendChart({super.key, required this.values, required this.labels, this.color});

  final List<double> values;
  final List<String> labels;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final tone = color ?? au.accent;
    final minY = values.reduce((a, b) => a < b ? a : b);
    final maxY = values.reduce((a, b) => a > b ? a : b);
    final pad = (maxY - minY).abs() < 1 ? 2.0 : (maxY - minY) * 0.15;
    final rtl = Directionality.of(context) == TextDirection.rtl;
    final spots = [
      for (var i = 0; i < values.length; i++) FlSpot((rtl ? values.length - 1 - i : i).toDouble(), values[i]),
    ]..sort((a, b) => a.x.compareTo(b.x));

    return Semantics(
      label: '${labels.first} ${values.first.toStringAsFixed(1)} → ${labels.last} ${values.last.toStringAsFixed(1)}',
      child: LineChart(
        LineChartData(
          minY: minY - pad,
          maxY: maxY + pad,
          gridData: FlGridData(
            drawVerticalLine: false,
            getDrawingHorizontalLine: (_) => FlLine(color: au.border, strokeWidth: 1, dashArray: [4, 4]),
          ),
          borderData: FlBorderData(show: false),
          titlesData: FlTitlesData(
            topTitles: const AxisTitles(),
            rightTitles: const AxisTitles(),
            leftTitles: AxisTitles(
              sideTitles: SideTitles(
                showTitles: true,
                reservedSize: 40,
                getTitlesWidget: (v, meta) => Text(v.toStringAsFixed(0), style: au.text.caption.copyWith(color: au.textMuted)),
              ),
            ),
            bottomTitles: AxisTitles(
              sideTitles: SideTitles(
                showTitles: true,
                interval: (values.length / 4).ceilToDouble().clamp(1, 1000),
                getTitlesWidget: (v, meta) {
                  final i = v.round();
                  final idx = rtl ? values.length - 1 - i : i;
                  if (idx < 0 || idx >= labels.length) return const SizedBox.shrink();
                  return Padding(
                    padding: const EdgeInsets.only(top: 6),
                    child: Text(labels[idx], style: au.text.caption.copyWith(color: au.textMuted)),
                  );
                },
              ),
            ),
          ),
          lineTouchData: LineTouchData(
            touchTooltipData: LineTouchTooltipData(
              getTooltipColor: (_) => au.surfaceRaised,
              getTooltipItems: (spots) => [
                for (final s in spots) LineTooltipItem(s.y.toStringAsFixed(1), au.text.label.copyWith(color: tone)),
              ],
            ),
          ),
          lineBarsData: [
            LineChartBarData(
              spots: spots,
              isCurved: true,
              curveSmoothness: 0.25,
              color: tone,
              barWidth: 3,
              dotData: FlDotData(
                getDotPainter: (_, _, _, _) => FlDotCirclePainter(radius: 3.5, color: au.background, strokeColor: tone, strokeWidth: 2),
              ),
              belowBarData: BarAreaData(
                show: true,
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [tone.withValues(alpha: 0.28), tone.withValues(alpha: 0)],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
