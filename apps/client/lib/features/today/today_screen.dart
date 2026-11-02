import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import '../habits/habits_providers.dart';
import '../habits/habits_screen.dart';
import '../logger/logger_controller.dart';
import '../notifications/notifications_providers.dart';

final todayProvider = FutureProvider.autoDispose<Fetched<Today>>(
  (ref) => ref.watch(todayRepositoryProvider).today(DateTime.now()),
);

class TodayScreen extends ConsumerWidget {
  const TodayScreen({super.key});

  String _greeting(BuildContext context) {
    final hour = DateTime.now().hour;
    if (hour < 12) return context.l10n.goodMorning;
    if (hour < 18) return context.l10n.goodAfternoon;
    return context.l10n.goodEvening;
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final l10n = context.l10n;
    final today = ref.watch(todayProvider);
    final user = ref.watch(authControllerProvider.select((s) => s.user));
    final unreadNotifications = ref.watch(notificationsUnreadProvider).value ?? 0;
    final name = today.value?.value.greetingName ?? user?.firstName ?? user?.name.split(' ').first ?? '';

    Future<void> refresh() async {
      ref.invalidate(habitsProvider(dateOnly(DateTime.now())));
      ref.invalidate(notificationsUnreadProvider);
      await ref.refresh(todayProvider.future);
    }

    return Scaffold(
      body: RefreshIndicator(
        color: au.accent,
        onRefresh: refresh,
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
          slivers: [
            SliverSafeArea(
              bottom: false,
              sliver: SliverPadding(
                padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s4, AuSpacing.s3, AuSpacing.s4),
                sliver: SliverToBoxAdapter(
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(context.formatDate(DateTime.now(), pattern: 'EEEE d MMMM').toUpperCase(),
                                style: au.text.overline.copyWith(color: au.accent)),
                            const SizedBox(height: AuSpacing.s1),
                            Text(name.isEmpty ? _greeting(context) : '${_greeting(context)}, $name', style: au.text.h2),
                          ],
                        ),
                      ),
                      if (today.value != null)
                        AuStreakFlame(
                          count: today.value!.value.streak.current,
                          size: 24,
                          semanticLabel: l10n.streakLabel(today.value!.value.streak.current),
                        ),
                      IconButton(
                        tooltip: l10n.notifications,
                        onPressed: () => context.push(Routes.notifications),
                        icon: Badge(
                          isLabelVisible: unreadNotifications > 0,
                          label: Text('$unreadNotifications'),
                          backgroundColor: au.danger,
                          child: const Icon(Icons.notifications_none_rounded),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SliverToBoxAdapter(child: OfflineBanner()),
            SliverPadding(
              padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, 0, AuSpacing.s5, AuSpacing.s16),
              sliver: SliverList.list(
                children: [
                  const _ResumeCard(),
                  today.when(
                    loading: () => const PageSkeleton(count: 4),
                    error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(todayProvider)),
                    data: (fetched) => _TodayContent(today: fetched.value, fromCache: fetched.fromCache),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ResumeCard extends ConsumerWidget {
  const _ResumeCard();

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
            Icon(Icons.play_circle_fill_rounded, color: au.accent, size: 40),
            const SizedBox(width: AuSpacing.s3),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(context.l10n.workoutInProgress.toUpperCase(), style: au.text.overline.copyWith(color: au.accent)),
                  Text(active.name ?? context.l10n.workout, style: au.text.title),
                  Text(context.l10n.startedAt(context.formatTime(active.startedAt)), style: au.text.caption.copyWith(color: au.textMuted)),
                ],
              ),
            ),
            AuButton(label: context.l10n.resume, size: AuButtonSize.compact, expand: false, onPressed: () => context.push(Routes.logger)),
          ],
        ),
      ),
    );
  }
}

class _TodayContent extends ConsumerWidget {
  const _TodayContent({required this.today, required this.fromCache});

  final Today today;
  final bool fromCache;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final l10n = context.l10n;
    final n = today.nutrition;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        CachedNotice(visible: fromCache),
        if (today.checkinDue != null) ...[
          AuCard(
            onTap: () => context.push(Routes.checkin(today.checkinDue!.id)),
            child: Row(
              children: [
                AuIconTile(icon: Icons.assignment_turned_in_outlined, color: au.warning),
                const SizedBox(width: AuSpacing.s3),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(today.checkinDue!.formName ?? l10n.checkIn, style: au.text.title),
                      Text(l10n.checkinDueOn(context.relativeDay(parseApiDate(today.checkinDue!.dueOn))),
                          style: au.text.caption.copyWith(color: au.warning)),
                    ],
                  ),
                ),
                Text(l10n.start, style: au.text.label.copyWith(color: au.accent)),
              ],
            ),
          ),
          const SizedBox(height: AuSpacing.s4),
        ],
        _WorkoutHero(workout: today.workout),
        const SizedBox(height: AuSpacing.s4),
        AuCard(
          onTap: () => context.go(Routes.nutrition),
          child: Row(
            children: [
              AuMacroRings(
                size: 132,
                consumed: n.consumed,
                targets: n.targets,
                center: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(context.formatNumber(n.consumed.kcal, maxDecimals: 0), style: au.text.title),
                    Text(context.core.unitKcal, style: au.text.caption.copyWith(color: au.textMuted)),
                  ],
                ),
              ),
              const SizedBox(width: AuSpacing.s4),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(l10n.nutritionToday.toUpperCase(), style: au.text.overline.copyWith(color: au.textMuted)),
                    const SizedBox(height: AuSpacing.s2),
                    _MacroLine(color: au.protein, label: context.core.protein, value: n.consumed.proteinG, target: n.targets?.proteinG),
                    _MacroLine(color: au.carbs, label: context.core.carbs, value: n.consumed.carbsG, target: n.targets?.carbsG),
                    _MacroLine(color: au.fat, label: context.core.fat, value: n.consumed.fatG, target: n.targets?.fatG),
                    const SizedBox(height: AuSpacing.s2),
                    Row(
                      children: [
                        Icon(Icons.water_drop_outlined, size: 16, color: au.info),
                        const SizedBox(width: AuSpacing.s1),
                        Text(
                          '${Units.formatWater(n.waterMl, ref.watch(unitsProvider), locale: context.localeName)} / '
                          '${Units.formatWater(n.waterTargetMl, ref.watch(unitsProvider), locale: context.localeName)}',
                          style: au.text.caption.copyWith(color: au.textSecondary),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: AuSpacing.s6),
        AuSectionHeader(
          title: l10n.habitsToday(today.habits.completed, today.habits.total),
          actionLabel: context.core.seeAll,
          onAction: () => context.push(Routes.habits),
        ),
        const HabitsPreview(limit: 3),
        if (today.nextSession != null) ...[
          const SizedBox(height: AuSpacing.s6),
          AuSectionHeader(title: l10n.nextSession, actionLabel: context.core.seeAll, onAction: () => context.push(Routes.calendar)),
          AuCard(
            onTap: () => context.push(Routes.calendar),
            child: Row(
              children: [
                const AuIconTile(icon: Icons.event_available_rounded),
                const SizedBox(width: AuSpacing.s3),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(today.nextSession!.event.title, style: au.text.title),
                      Text(context.formatDayTime(today.nextSession!.event.startsAt), style: au.text.caption.copyWith(color: au.textSecondary)),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
        if (today.coach != null) ...[
          const SizedBox(height: AuSpacing.s6),
          AuCard(
            onTap: () => context.push(Routes.messages),
            child: Row(
              children: [
                AuAvatar(name: today.coach!.name, imageUrl: today.coach!.avatarUrl, ring: true),
                const SizedBox(width: AuSpacing.s3),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(today.coach!.name, style: au.text.title),
                      Text(
                        today.unreadMessages > 0 ? context.core.notificationsBadge(today.unreadMessages) : l10n.messageYourCoach,
                        style: au.text.caption.copyWith(color: today.unreadMessages > 0 ? au.accent : au.textSecondary),
                      ),
                    ],
                  ),
                ),
                if (today.unreadMessages > 0) AuBadge.count(today.unreadMessages),
              ],
            ),
          ),
        ],
      ],
    );
  }
}

class _MacroLine extends StatelessWidget {
  const _MacroLine({required this.color, required this.label, required this.value, required this.target});

  final Color color;
  final String label;
  final double value;
  final double? target;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Padding(
      padding: const EdgeInsetsDirectional.only(bottom: 2),
      child: Row(
        children: [
          Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
          const SizedBox(width: AuSpacing.s2),
          Expanded(child: Text(label, style: au.text.caption.copyWith(color: au.textSecondary))),
          Text(
            target == null
                ? '${context.formatNumber(value, maxDecimals: 0)} ${context.core.unitG}'
                : '${context.formatNumber(value, maxDecimals: 0)}/${context.formatNumber(target!, maxDecimals: 0)} ${context.core.unitG}',
            style: au.text.caption,
          ),
        ],
      ),
    );
  }
}

class _WorkoutHero extends StatelessWidget {
  const _WorkoutHero({required this.workout});

  final TodayWorkout? workout;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final w = workout;
    if (w == null) {
      return AuCard(
        variant: AuCardVariant.sunken,
        padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
        child: Row(
          children: [
            Icon(Icons.spa_outlined, color: au.accent, size: 32),
            const SizedBox(width: AuSpacing.s4),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(l10n.restDay, style: au.text.title),
                  Text(l10n.restDayHint, style: au.text.bodySmall.copyWith(color: au.textSecondary)),
                ],
              ),
            ),
          ],
        ),
      );
    }
    final done = w.status == 'completed';
    return AuCard(
      variant: AuCardVariant.hero,
      padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(l10n.todaysWorkout.toUpperCase(), style: au.text.overline.copyWith(color: au.accent)),
              const Spacer(),
              if (w.weekNumber != null && w.dayNumber != null)
                Text('${l10n.weekN(w.weekNumber!)} · ${l10n.dayN(w.dayNumber!)}', style: au.text.caption.copyWith(color: au.textMuted)),
            ],
          ),
          const SizedBox(height: AuSpacing.s3),
          Text(w.name, style: au.text.h2),
          const SizedBox(height: AuSpacing.s2),
          Row(
            children: [
              if (w.estimatedMinutes != null) ...[
                Icon(Icons.timer_outlined, size: 16, color: au.textSecondary),
                const SizedBox(width: AuSpacing.s1),
                Text(context.core.minutesShort(w.estimatedMinutes!), style: au.text.bodySmall.copyWith(color: au.textSecondary)),
                const SizedBox(width: AuSpacing.s4),
              ],
              Icon(Icons.fitness_center_rounded, size: 16, color: au.textSecondary),
              const SizedBox(width: AuSpacing.s1),
              Text(l10n.exercisesCount(w.exerciseCount), style: au.text.bodySmall.copyWith(color: au.textSecondary)),
            ],
          ),
          const SizedBox(height: AuSpacing.s5),
          if (done)
            Row(
              children: [
                Icon(Icons.check_circle_rounded, color: au.success),
                const SizedBox(width: AuSpacing.s2),
                Text(l10n.completedToday, style: au.text.label.copyWith(color: au.success)),
              ],
            )
          else
            AuButton(
              key: const Key('today-start'),
              label: w.status == 'in_progress' ? l10n.resumeWorkout : l10n.startWorkout,
              icon: Icons.play_arrow_rounded,
              onPressed: () => context.push(Routes.workout(w.workoutId, dayId: w.programDayId, assignmentId: w.programAssignmentId)),
            ),
        ],
      ),
    );
  }
}
