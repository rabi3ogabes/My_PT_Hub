import 'package:aurum_core/aurum_core.dart';
import 'package:collection/collection.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import '../logger/finish_screen.dart';
import 'train_providers.dart';

/// PR board: best record per exercise and type.
class RecordsScreen extends ConsumerWidget {
  const RecordsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final records = ref.watch(recordsProvider);
    return AuPage(
      title: l10n.personalRecords,
      onRefresh: () => ref.refresh(recordsProvider.future),
      children: [
        records.when(
          loading: () => const PageSkeleton(),
          error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(recordsProvider)),
          data: (fetched) => fetched.value.isEmpty
              ? AuEmptyState(
                  icon: Icons.emoji_events_outlined,
                  title: l10n.noRecordsTitle,
                  message: l10n.noRecordsHint,
                  actionLabel: l10n.goToTrain,
                  onAction: () => context.go(Routes.train),
                )
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    CachedNotice(visible: fetched.fromCache),
                    PrBoard(records: fetched.value),
                  ],
                ),
        ),
      ],
    );
  }
}

/// Grouped PR cards (also used on the Progress tab).
class PrBoard extends ConsumerWidget {
  const PrBoard({super.key, required this.records, this.limit});

  final List<PersonalRecord> records;
  final int? limit;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final units = ref.watch(unitsProvider);
    final grouped = groupBy(records, (PersonalRecord r) => r.exerciseId).entries.toList()
      ..sort((a, b) => b.value.map((r) => r.achievedAt).max.compareTo(a.value.map((r) => r.achievedAt).max));
    final shown = limit == null ? grouped : grouped.take(limit!);
    return Column(
      children: [
        for (final entry in shown) ...[
          AuCard(
            onTap: () => context.push(Routes.exercise(entry.key, entry.value.first.exerciseName)),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.emoji_events_rounded, color: au.accent, size: 20),
                    const SizedBox(width: AuSpacing.s2),
                    Expanded(child: Text(entry.value.first.exerciseName, style: au.text.title)),
                    AuDirectionalIcon(Icons.chevron_right_rounded, color: au.textMuted),
                  ],
                ),
                const SizedBox(height: AuSpacing.s3),
                Wrap(
                  spacing: AuSpacing.s4,
                  runSpacing: AuSpacing.s2,
                  children: [
                    for (final r in entry.value)
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(prTypeLabel(context, r.type).toUpperCase(), style: au.text.overline.copyWith(color: au.textMuted)),
                          Text(
                            r.type == 'reps' ? context.formatNumber(r.value, maxDecimals: 0) : displayWeight(context, r.value, units),
                            style: au.text.label.copyWith(color: au.accent),
                          ),
                          Text(context.formatDate(r.achievedAt.toLocal(), pattern: 'yMMMd'), style: au.text.caption.copyWith(color: au.textMuted)),
                        ],
                      ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: AuSpacing.s3),
        ],
      ],
    );
  }
}
