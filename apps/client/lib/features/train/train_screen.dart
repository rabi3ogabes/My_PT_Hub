import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import '../logger/logger_controller.dart';
import 'train_providers.dart';

/// Program overview: weeks × days, quick links to history and records.
class TrainScreen extends ConsumerStatefulWidget {
  const TrainScreen({super.key});

  @override
  ConsumerState<TrainScreen> createState() => _TrainScreenState();
}

class _TrainScreenState extends ConsumerState<TrainScreen> {
  int? _week;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final program = ref.watch(myProgramProvider);
    return AuPage(
      title: l10n.navTrain,
      onRefresh: () => ref.refresh(myProgramProvider.future),
      actions: [
        IconButton(tooltip: l10n.history, icon: const Icon(Icons.history_rounded), onPressed: () => context.push('${Routes.train}/history')),
        IconButton(tooltip: l10n.personalRecords, icon: const Icon(Icons.emoji_events_outlined), onPressed: () => context.push('${Routes.train}/records')),
      ],
      children: [
        const _ResumeBanner(),
        program.when(
          loading: () => const PageSkeleton(),
          error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(myProgramProvider)),
          data: (fetched) {
            final mine = fetched.value;
            if (mine == null) {
              final hasCoach = ref.watch(authControllerProvider.select((s) => s.hasCoach));
              return AuEmptyState(
                icon: Icons.calendar_view_week_rounded,
                title: l10n.noProgramTitle,
                message: hasCoach ? l10n.noProgramCoachHint : l10n.noProgramSoloHint,
                actionLabel: hasCoach ? l10n.messageCoach : l10n.viewHistory,
                onAction: () => hasCoach ? context.push(Routes.messages) : context.push('${Routes.train}/history'),
              );
            }
            final weeks = mine.program.weeks;
            final current = (_week ?? mine.assignment.currentWeek).clamp(1, weeks.isEmpty ? 1 : weeks.length);
            final week = weeks.where((w) => w.number == current).firstOrNull ?? weeks.firstOrNull;
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                CachedNotice(visible: fetched.fromCache),
                _ProgramHeader(mine: mine),
                const SizedBox(height: AuSpacing.s5),
                SizedBox(
                  height: 44,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemCount: weeks.length,
                    separatorBuilder: (_, _) => const SizedBox(width: AuSpacing.s2),
                    itemBuilder: (context, i) {
                      final w = weeks[i];
                      return AuChip(
                        label: w.isDeload ? '${l10n.weekN(w.number)} · ${l10n.deload}' : l10n.weekN(w.number),
                        selected: w.number == current,
                        onSelected: (_) => setState(() => _week = w.number),
                      );
                    },
                  ),
                ),
                if (week?.phase != null || week?.notes != null) ...[
                  const SizedBox(height: AuSpacing.s3),
                  Text(
                    [week?.phase, week?.notes].whereType<String>().join(' — '),
                    style: context.au.text.bodySmall.copyWith(color: context.au.textSecondary),
                  ),
                ],
                const SizedBox(height: AuSpacing.s4),
                if (week != null)
                  for (final day in week.days) ...[
                    _DayCard(mine: mine, weekNumber: week.number, day: day),
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

class _ResumeBanner extends ConsumerWidget {
  const _ResumeBanner();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final active = ref.watch(activeLocalSessionProvider).value;
    if (active == null) return const SizedBox.shrink();
    final au = context.au;
    return Padding(
      padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s4),
      child: AuCard(
        variant: AuCardVariant.hero,
        onTap: () => context.push(Routes.logger),
        child: Row(
          children: [
            Icon(Icons.play_circle_fill_rounded, color: au.accent, size: 36),
            const SizedBox(width: AuSpacing.s3),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(context.l10n.workoutInProgress.toUpperCase(), style: au.text.overline.copyWith(color: au.accent)),
                  Text(active.name ?? context.l10n.workout, style: au.text.title),
                ],
              ),
            ),
            Text(context.l10n.resume, style: au.text.label.copyWith(color: au.accent)),
          ],
        ),
      ),
    );
  }
}

class _ProgramHeader extends StatelessWidget {
  const _ProgramHeader({required this.mine});

  final MyProgram mine;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final p = mine.program;
    return AuCard(
      variant: AuCardVariant.hero,
      padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(context.l10n.yourProgram.toUpperCase(), style: au.text.overline.copyWith(color: au.accent)),
          const SizedBox(height: AuSpacing.s2),
          Text(p.name, style: au.text.h3),
          if (p.description != null) ...[
            const SizedBox(height: AuSpacing.s1),
            Text(p.description!, style: au.text.bodySmall.copyWith(color: au.textSecondary), maxLines: 2, overflow: TextOverflow.ellipsis),
          ],
          const SizedBox(height: AuSpacing.s4),
          Row(
            children: [
              Text(context.l10n.weekOf(mine.assignment.currentWeek, p.weeksCount ?? p.weeks.length), style: au.text.label),
              const Spacer(),
              Text('${mine.assignment.progressPct.round()}%', style: au.text.label.copyWith(color: au.accent)),
            ],
          ),
          const SizedBox(height: AuSpacing.s2),
          ClipRRect(
            borderRadius: BorderRadius.circular(AuRadius.pill),
            child: LinearProgressIndicator(value: mine.assignment.progressPct / 100, minHeight: 6, backgroundColor: au.border),
          ),
        ],
      ),
    );
  }
}

class _DayCard extends StatelessWidget {
  const _DayCard({required this.mine, required this.weekNumber, required this.day});

  final MyProgram mine;
  final int weekNumber;
  final ProgramDay day;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final date = programDayDate(mine.assignment.startDate, weekNumber, day.dayNumber);
    final unlockedThrough = mine.unlockedThrough == null ? null : parseApiDate(mine.unlockedThrough!);
    final locked = unlockedThrough != null && date.isAfter(unlockedThrough);
    final isToday = DateUtils.isSameDay(date, DateTime.now());
    final workout = day.workout;

    return Opacity(
      opacity: locked ? 0.5 : 1,
      child: AuCard(
        variant: isToday ? AuCardVariant.hero : AuCardVariant.surface,
        onTap: workout == null || locked
            ? null
            : () => context.push(Routes.workout(workout.id, dayId: day.id, assignmentId: mine.assignment.id)),
        child: Row(
          children: [
            Container(
              width: 52,
              padding: const EdgeInsetsDirectional.symmetric(vertical: AuSpacing.s2),
              decoration: BoxDecoration(
                color: isToday ? au.accent.withValues(alpha: 0.14) : au.surfaceSunken,
                borderRadius: BorderRadius.circular(AuRadius.sm),
              ),
              child: Column(
                children: [
                  Text(context.formatDate(date, pattern: 'EEE'), style: au.text.caption.copyWith(color: au.textMuted)),
                  Text(context.formatDate(date, pattern: 'd'), style: au.text.title.copyWith(color: isToday ? au.accent : au.textPrimary)),
                ],
              ),
            ),
            const SizedBox(width: AuSpacing.s4),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    day.isRest ? l10n.restDay : (workout?.name ?? day.title ?? l10n.dayN(day.dayNumber)),
                    style: au.text.title,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    day.isRest
                        ? l10n.restDayHint
                        : [
                            if (day.title != null && workout != null) day.title!,
                            if (workout?.estimatedMinutes != null) context.core.minutesShort(workout!.estimatedMinutes!),
                          ].join(' · '),
                    style: au.text.caption.copyWith(color: au.textSecondary),
                  ),
                ],
              ),
            ),
            if (locked)
              Icon(Icons.lock_outline_rounded, color: au.textMuted)
            else if (workout != null)
              AuDirectionalIcon(Icons.chevron_right_rounded, color: au.textMuted)
            else
              Icon(Icons.spa_outlined, color: au.textMuted),
          ],
        ),
      ),
    );
  }
}
