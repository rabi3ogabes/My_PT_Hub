import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'train_providers.dart';

/// Completed sessions (server history plus sessions still syncing).
class HistoryScreen extends ConsumerWidget {
  const HistoryScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final au = context.au;
    final history = ref.watch(sessionHistoryProvider);
    final local = ref.watch(localCompletedSessionsProvider).value ?? const [];
    final units = ref.watch(unitsProvider);

    return AuPage(
      title: l10n.history,
      onRefresh: () async {
        ref.invalidate(localCompletedSessionsProvider);
        await ref.refresh(sessionHistoryProvider.future);
      },
      children: [
        history.when(
          loading: () => const PageSkeleton(),
          error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(sessionHistoryProvider)),
          data: (h) {
            final serverIds = h.items.map((s) => s.id).toSet();
            final pending = local.where((l) => !serverIds.contains(l.id)).toList();
            if (h.items.isEmpty && pending.isEmpty) {
              return AuEmptyState(
                icon: Icons.history_rounded,
                title: l10n.noHistoryTitle,
                message: l10n.noHistoryHint,
                actionLabel: l10n.goToTrain,
                onAction: () => context.go(Routes.train),
              );
            }
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                for (final p in pending) ...[
                  AuCard(
                    child: Row(
                      children: [
                        const AuIconTile(icon: Icons.cloud_upload_outlined),
                        const SizedBox(width: AuSpacing.s3),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(p.name ?? l10n.workout, style: au.text.title),
                              Text('${context.relativeDay(p.startedAt.toLocal())} · ${l10n.waitingToSync}',
                                  style: au.text.caption.copyWith(color: au.textMuted)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AuSpacing.s3),
                ],
                for (final s in h.items) ...[
                  AuCard(
                    child: Row(
                      children: [
                        const AuIconTile(icon: Icons.check_rounded),
                        const SizedBox(width: AuSpacing.s3),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(s.name ?? l10n.workout, style: au.text.title),
                              Text(
                                [
                                  if (s.startedAt != null) context.relativeDay(s.startedAt!.toLocal()),
                                  if (s.durationSeconds != null) formatClock(Duration(seconds: s.durationSeconds!)),
                                  if (s.totalVolumeKg != null) displayWeight(context, s.totalVolumeKg!, units, maxDecimals: 0),
                                ].join(' · '),
                                style: au.text.caption.copyWith(color: au.textMuted),
                              ),
                            ],
                          ),
                        ),
                        if (s.personalRecords.isNotEmpty) AuBadge(label: 'PR ×${s.personalRecords.length}'),
                      ],
                    ),
                  ),
                  const SizedBox(height: AuSpacing.s3),
                ],
                if (h.hasMore)
                  AuButton.secondary(
                    label: l10n.loadMore,
                    loading: h.loadingMore,
                    onPressed: () => ref.read(sessionHistoryProvider.notifier).loadMore(),
                  ),
              ],
            );
          },
        ),
      ],
    );
  }
}
