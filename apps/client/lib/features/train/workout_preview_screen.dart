import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import '../logger/logger_controller.dart';
import '../logger/logger_widgets.dart';
import 'train_providers.dart';

/// Workout preview: blocks, equipment, duration, thumbnails, then Start.
/// The workout (with last performance) is cached for offline logging.
class WorkoutPreviewScreen extends ConsumerStatefulWidget {
  const WorkoutPreviewScreen({super.key, required this.workoutId, this.programDayId, this.assignmentId});

  final String workoutId;
  final String? programDayId;
  final String? assignmentId;

  @override
  ConsumerState<WorkoutPreviewScreen> createState() => _WorkoutPreviewScreenState();
}

class _WorkoutPreviewScreenState extends ConsumerState<WorkoutPreviewScreen> {
  bool _starting = false;

  Future<void> _start(Workout workout) async {
    setState(() => _starting = true);
    try {
      await ref.read(loggerControllerProvider.notifier).start(
            workout,
            programDayId: widget.programDayId,
            assignmentId: widget.assignmentId,
          );
      AuHaptics.medium();
      if (mounted) await context.push(Routes.logger);
    } on Object catch (e) {
      if (mounted) showError(context, e);
    } finally {
      if (mounted) setState(() => _starting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final au = context.au;
    final workout = ref.watch(workoutProvider(widget.workoutId));
    final active = ref.watch(activeLocalSessionProvider).value;

    return workout.when(
      loading: () => Scaffold(appBar: AppBar(), body: const AuSkeletonList()),
      error: (e, _) => Scaffold(appBar: AppBar(), body: AuErrorState(error: e, onRetry: () => ref.invalidate(workoutProvider(widget.workoutId)))),
      data: (fetched) {
        final w = fetched.value;
        final resuming = active?.workoutId == w.id;
        return AuPage(
          title: w.name,
          overline: w.difficulty,
          onRefresh: () => ref.refresh(workoutProvider(widget.workoutId).future),
          bottom: SafeArea(
            child: Padding(
              padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s2, AuSpacing.s5, AuSpacing.s3),
              child: AuButton(
                key: const Key('start-workout'),
                label: resuming ? l10n.resumeWorkout : l10n.startWorkout,
                icon: Icons.play_arrow_rounded,
                loading: _starting,
                onPressed: () => resuming ? context.push(Routes.logger) : _start(w),
              ),
            ),
          ),
          children: [
            CachedNotice(visible: fetched.fromCache),
            Row(
              children: [
                if (w.estimatedMinutes != null)
                  Expanded(child: AuStatTile(label: context.core.duration, value: '${w.estimatedMinutes}', unit: context.core.unitMin, icon: Icons.timer_outlined)),
                if (w.estimatedMinutes != null) const SizedBox(width: AuSpacing.s3),
                Expanded(child: AuStatTile(label: l10n.exercisesLabel, value: '${w.exerciseCount}', icon: Icons.fitness_center_rounded)),
                const SizedBox(width: AuSpacing.s3),
                Expanded(child: AuStatTile(label: l10n.setsLabel, value: '${w.setCount}', icon: Icons.repeat_rounded)),
              ],
            ),
            if (w.description != null) ...[
              const SizedBox(height: AuSpacing.s4),
              Text(w.description!, style: au.text.body.copyWith(color: au.textSecondary)),
            ],
            if (w.equipmentSummary.isNotEmpty) ...[
              const SizedBox(height: AuSpacing.s5),
              AuSectionHeader(title: l10n.equipment),
              Wrap(
                spacing: AuSpacing.s2,
                runSpacing: AuSpacing.s2,
                children: [for (final e in w.equipmentSummary) AuChip(label: e, icon: Icons.build_circle_outlined)],
              ),
            ],
            const SizedBox(height: AuSpacing.s3),
            Row(
              children: [
                Icon(Icons.offline_pin_rounded, size: 16, color: au.success),
                const SizedBox(width: AuSpacing.s1),
                Expanded(
                  child: Text(
                    w.downloadSizeBytes == null
                        ? l10n.availableOffline
                        : '${l10n.availableOffline} · ${l10n.videoDownloadSize(_mb(w.downloadSizeBytes!))}',
                    style: au.text.caption.copyWith(color: au.textSecondary),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AuSpacing.s5),
            for (final block in w.blocks) ...[
              _BlockPreview(block: block),
              const SizedBox(height: AuSpacing.s4),
            ],
          ],
        );
      },
    );
  }

  String _mb(int bytes) => (bytes / (1024 * 1024)).toStringAsFixed(1);
}

class _BlockPreview extends StatelessWidget {
  const _BlockPreview({required this.block});

  final WorkoutBlock block;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final meta = <String>[
      if (block.rounds != null) l10n.roundsCount(block.rounds!),
      if (block.workSeconds != null) '${l10n.work} ${block.workSeconds}s',
      if (block.restSeconds != null) '${l10n.rest} ${block.restSeconds}s',
      if (block.timeCapSeconds != null) '${l10n.timeCap} ${formatClock(Duration(seconds: block.timeCapSeconds!))}',
    ];
    return AuCard(
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s4, AuSpacing.s4, AuSpacing.s4, AuSpacing.s2),
            child: Row(
              children: [
                AuBadge(label: blockTypeLabel(context, block.type).toUpperCase(), solid: false),
                const SizedBox(width: AuSpacing.s2),
                Expanded(child: Text(block.name ?? '', style: au.text.title)),
                Text(meta.join(' · '), style: au.text.caption.copyWith(color: au.textMuted)),
              ],
            ),
          ),
          for (final we in block.exercises)
            AuListRow(
              leading: _Thumb(url: we.exercise.video?.posterUrl),
              title: we.exercise.name,
              subtitle: _setsSummary(context, we),
              showChevron: false,
            ),
        ],
      ),
    );
  }

  String _setsSummary(BuildContext context, WorkoutExercise we) {
    final sets = we.sets;
    if (sets.isEmpty) return we.exercise.primaryMuscle ?? '';
    final first = sets.first;
    final reps = first.repsMin != null && first.repsMax != null && first.repsMin != first.repsMax
        ? '${first.repsMin}–${first.repsMax}'
        : '${first.repsMax ?? first.repsMin ?? ''}';
    final duration = first.durationSeconds == null ? '' : formatClock(Duration(seconds: first.durationSeconds!));
    return '${sets.length} × ${reps.isNotEmpty ? reps : duration}${we.exercise.primaryMuscle == null ? '' : ' · ${we.exercise.primaryMuscle}'}';
  }
}

class _Thumb extends StatelessWidget {
  const _Thumb({this.url});

  final String? url;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Container(
      width: 52,
      height: 52,
      decoration: BoxDecoration(color: au.surfaceSunken, borderRadius: BorderRadius.circular(AuRadius.sm)),
      clipBehavior: Clip.antiAlias,
      child: url == null
          ? Icon(Icons.play_circle_outline_rounded, color: au.textMuted)
          : Stack(
              fit: StackFit.expand,
              children: [
                Image.network(url!, fit: BoxFit.cover, errorBuilder: (_, _, _) => const SizedBox.shrink()),
                const Center(child: Icon(Icons.play_arrow_rounded, color: Colors.white70)),
              ],
            ),
    );
  }
}
