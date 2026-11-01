import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import 'audio_cues.dart';
import 'logger_controller.dart';
import 'logger_sheets.dart';
import 'logger_state.dart';

String blockTypeLabel(BuildContext context, BlockType type) {
  final l10n = context.l10n;
  return switch (type) {
    BlockType.straight => l10n.blockStraight,
    BlockType.superset => l10n.blockSuperset,
    BlockType.giant => l10n.blockGiant,
    BlockType.circuit => l10n.blockCircuit,
    BlockType.tabata => l10n.blockTabata,
    BlockType.emom => l10n.blockEmom,
    BlockType.amrap => l10n.blockAmrap,
    BlockType.interval => l10n.blockInterval,
    BlockType.warmup => l10n.blockWarmup,
    BlockType.cooldown => l10n.blockCooldown,
  };
}

/// A block: header, optional timer (Tabata/EMOM/interval/AMRAP) and its
/// exercises. Cycling blocks get a letter per exercise (A1, A2 …).
class BlockSection extends StatelessWidget {
  const BlockSection({super.key, required this.block, required this.state});

  final LoggerBlock block;
  final LoggerState state;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final showHeader = block.type != BlockType.straight || block.name != null;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (showHeader)
          Padding(
            padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s3),
            child: Row(
              children: [
                AuBadge(label: blockTypeLabel(context, block.type).toUpperCase(), tone: AuBadgeTone.accent, solid: false),
                const SizedBox(width: AuSpacing.s2),
                if (block.name != null) Expanded(child: Text(block.name!, style: au.text.title)),
                if (block.type.isCycling && block.rounds != null)
                  Text(context.l10n.roundsCount(block.rounds!), style: au.text.caption.copyWith(color: au.textMuted)),
              ],
            ),
          ),
        if (block.notes != null && block.notes!.isNotEmpty)
          Padding(
            padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s3),
            child: Text(block.notes!, style: au.text.bodySmall.copyWith(color: au.textSecondary)),
          ),
        if (block.type.isTimed) IntervalTimerCard(block: block, clock: state.clocks[block.id]),
        if (block.type == BlockType.amrap)
          AmrapCard(block: block, clock: state.clocks[block.id], rounds: state.amrapRounds[block.id] ?? 0),
        if (block.type.isTimed || block.type == BlockType.amrap) const SizedBox(height: AuSpacing.s3),
        for (var i = 0; i < block.exercises.length; i++) ...[
          ExerciseCard(
            exercise: block.exercises[i],
            state: state,
            label: block.type.isCycling ? '${String.fromCharCode(65 + state.blocks.indexOf(block) % 26)}${i + 1}' : null,
          ),
          if (i < block.exercises.length - 1)
            block.type.isCycling
                ? Padding(
                    padding: const EdgeInsetsDirectional.symmetric(vertical: AuSpacing.s1),
                    child: Icon(Icons.swap_vert_rounded, color: au.accent, size: 18),
                  )
                : const SizedBox(height: AuSpacing.s3),
        ],
      ],
    );
  }
}

/// One exercise with its set rows.
class ExerciseCard extends ConsumerWidget {
  const ExerciseCard({super.key, required this.exercise, required this.state, this.label});

  final LoggerExercise exercise;
  final LoggerState state;
  final String? label;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final l10n = context.l10n;
    final units = ref.watch(unitsProvider);
    final controller = ref.read(loggerControllerProvider.notifier);
    final ex = exercise.exercise;
    final unit = weightUnitLabel(context, units);
    final showWeight = !ex.isBodyweight && (ex.tracksWeight || ex.metrics.isEmpty);
    final showReps = ex.tracksReps || (!ex.tracksTime && !ex.tracksDistance);
    final showDuration = ex.tracksTime;

    String? previousLabel(LoggerSet s) {
      final p = s.previous;
      if (p == null) return null;
      final parts = <String>[
        if (p.weightKg != null) context.formatNumber(Units.weightToDisplay(p.weightKg!, units)),
        if (p.reps != null) '${p.reps}',
        if (p.durationSeconds != null) formatClock(Duration(seconds: p.durationSeconds!)),
      ];
      return parts.isEmpty ? null : parts.join(' × ');
    }

    String? repsHint(LoggerSet s) {
      if (s.repsMin == null && s.repsMax == null) return null;
      if (s.repsMin != null && s.repsMax != null && s.repsMin != s.repsMax) return '${s.repsMin}–${s.repsMax}';
      return '${s.repsMax ?? s.repsMin}';
    }

    return AuCard(
      padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s3, AuSpacing.s3, AuSpacing.s2, AuSpacing.s2),
      variant: exercise.isDone ? AuCardVariant.sunken : AuCardVariant.surface,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              if (label != null) ...[
                Container(
                  padding: const EdgeInsetsDirectional.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(gradient: au.goldGradient, borderRadius: BorderRadius.circular(AuRadius.xs)),
                  child: Text(label!, style: au.text.label.copyWith(color: au.onAccent)),
                ),
                const SizedBox(width: AuSpacing.s2),
              ],
              _Thumb(exercise: ex),
              const SizedBox(width: AuSpacing.s3),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(ex.name, style: au.text.title, maxLines: 2, overflow: TextOverflow.ellipsis),
                    Text(
                      [
                        if (exercise.swappedFromName != null) l10n.swappedFrom(exercise.swappedFromName!),
                        if (exercise.tempo != null) l10n.tempo(exercise.tempo!),
                        if (exercise.restSeconds != null) l10n.restSeconds(exercise.restSeconds!),
                      ].join(' · '),
                      style: au.text.caption.copyWith(color: au.textMuted),
                    ),
                  ],
                ),
              ),
              if (exercise.alternativeIds.isNotEmpty || ex.alternatives.isNotEmpty)
                IconButton(
                  tooltip: l10n.swapExercise,
                  icon: const Icon(Icons.swap_horiz_rounded),
                  onPressed: () => showSwapSheet(context, ref, exercise),
                ),
              PopupMenuButton<String>(
                tooltip: l10n.exerciseOptions,
                icon: const Icon(Icons.more_vert_rounded),
                onSelected: (v) {
                  switch (v) {
                    case 'note':
                      showNoteSheet(
                        context,
                        title: l10n.exerciseNote,
                        initial: exercise.userNote,
                        onSave: (n) => controller.setExerciseNote(exercise.id, n),
                      );
                    case 'swap':
                      showSwapSheet(context, ref, exercise);
                    case 'history':
                      context.push(Routes.exercise(ex.id, ex.name));
                    case 'add':
                      controller.addSet(exercise.id);
                  }
                },
                itemBuilder: (_) => [
                  PopupMenuItem(value: 'note', child: Text(l10n.exerciseNote)),
                  PopupMenuItem(value: 'swap', child: Text(l10n.swapExercise)),
                  PopupMenuItem(value: 'add', child: Text(l10n.addSet)),
                  PopupMenuItem(value: 'history', child: Text(l10n.viewHistory)),
                ],
              ),
            ],
          ),
          if (exercise.coachNotes != null && exercise.coachNotes!.isNotEmpty)
            _NoteLine(icon: Icons.record_voice_over_outlined, text: exercise.coachNotes!),
          if (exercise.userNote != null) _NoteLine(icon: Icons.sticky_note_2_outlined, text: exercise.userNote!),
          const SizedBox(height: AuSpacing.s2),
          // Column headers.
          Padding(
            padding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s2),
            child: DefaultTextStyle(
              style: au.text.overline.copyWith(color: au.textMuted),
              child: Row(
                children: [
                  SizedBox(width: 36, child: Text(context.core.setLabel.toUpperCase())),
                  Expanded(flex: 5, child: Text(context.core.previous.toUpperCase())),
                  if (showWeight) Expanded(flex: 4, child: Text(unit.toUpperCase(), textAlign: TextAlign.center)),
                  if (showReps) ...[const SizedBox(width: AuSpacing.s2), Expanded(flex: 3, child: Text(context.core.reps.toUpperCase(), textAlign: TextAlign.center))],
                  if (showDuration) ...[const SizedBox(width: AuSpacing.s2), Expanded(flex: 3, child: Text(context.core.duration.toUpperCase(), textAlign: TextAlign.center))],
                  const SizedBox(width: 36 + 44 + AuSpacing.s1),
                ],
              ),
            ),
          ),
          for (final set in exercise.sets)
            AuSetRow(
              key: ValueKey(set.id),
              setNumber: set.setNumber,
              setType: set.setType,
              previous: previousLabel(set),
              onSameAsLast: set.completed ? null : () => controller.sameAsLast(set.id),
              weight: set.weightKg == null ? null : Units.weightToDisplay(set.weightKg!, units),
              reps: set.reps,
              durationSeconds: set.durationSeconds,
              showWeight: showWeight,
              showReps: showReps,
              showDuration: showDuration,
              weightUnit: unit,
              repsHint: repsHint(set),
              completed: set.completed,
              active: state.activeSetId == set.id,
              rpe: set.rpe,
              onRpeTap: () => showRpePicker(context, ref, set),
              onWeightChanged: (v) => controller.editWeight(set.id, v == null ? null : Units.weightFromDisplay(v, units)),
              onRepsChanged: (v) => controller.editReps(set.id, v),
              onDurationChanged: (v) => controller.editDuration(set.id, v),
              onToggleComplete: () => controller.toggleSet(set.id),
              onLongPress: () => showSetOptions(context, ref, set),
            ),
          if (state.applyPromptExerciseId == exercise.id)
            ApplyToRemainingBanner(
              onApply: () {
                AuHaptics.medium();
                controller.applyToRemaining(exercise.id);
              },
              onDismiss: controller.dismissApplyPrompt,
            ),
          Align(
            alignment: AlignmentDirectional.centerStart,
            child: TextButton.icon(
              onPressed: () => controller.addSet(exercise.id),
              icon: const Icon(Icons.add_rounded, size: 18),
              label: Text(l10n.addSet),
            ),
          ),
        ],
      ),
    );
  }
}

class _NoteLine extends StatelessWidget {
  const _NoteLine({required this.icon, required this.text});

  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Padding(
      padding: const EdgeInsetsDirectional.only(top: AuSpacing.s2),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 16, color: au.accent),
          const SizedBox(width: AuSpacing.s2),
          Expanded(child: Text(text, style: au.text.bodySmall.copyWith(color: au.textSecondary))),
        ],
      ),
    );
  }
}

class _Thumb extends StatelessWidget {
  const _Thumb({required this.exercise});

  final Exercise exercise;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final poster = exercise.video?.posterUrl;
    return Container(
      width: 44,
      height: 44,
      decoration: BoxDecoration(color: au.surfaceSunken, borderRadius: BorderRadius.circular(AuRadius.sm)),
      clipBehavior: Clip.antiAlias,
      child: poster == null
          ? Icon(Icons.fitness_center_rounded, size: 20, color: au.textMuted)
          : Image.network(poster, fit: BoxFit.cover, errorBuilder: (_, _, _) => Icon(Icons.fitness_center_rounded, color: au.textMuted)),
    );
  }
}

/// "Apply to remaining sets?" prompt shown after editing set 1.
class ApplyToRemainingBanner extends StatelessWidget {
  const ApplyToRemainingBanner({super.key, required this.onApply, required this.onDismiss});

  final VoidCallback onApply;
  final VoidCallback onDismiss;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Container(
      margin: const EdgeInsetsDirectional.only(top: AuSpacing.s2, end: AuSpacing.s2),
      padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s3, AuSpacing.s1, AuSpacing.s1, AuSpacing.s1),
      decoration: BoxDecoration(
        color: au.accent.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(AuRadius.sm),
        border: Border.all(color: au.accent.withValues(alpha: 0.4)),
      ),
      child: Row(
        children: [
          Icon(Icons.auto_fix_high_rounded, size: 18, color: au.accent),
          const SizedBox(width: AuSpacing.s2),
          Expanded(child: Text(context.l10n.applyToRemainingQuestion, style: au.text.bodySmall)),
          TextButton(key: const Key('apply-remaining'), onPressed: onApply, child: Text(context.core.apply)),
          IconButton(tooltip: context.core.dismiss, icon: const Icon(Icons.close_rounded, size: 18), onPressed: onDismiss),
        ],
      ),
    );
  }
}

/// Sticky rest countdown (wall-clock based; survives backgrounding).
class RestTimerBar extends ConsumerStatefulWidget {
  const RestTimerBar({super.key, required this.rest, this.nextLabel});

  final RestTimer rest;
  final String? nextLabel;

  @override
  ConsumerState<RestTimerBar> createState() => _RestTimerBarState();
}

class _RestTimerBarState extends ConsumerState<RestTimerBar> {
  Timer? _ticker;
  int _lastTickSecond = -1;

  @override
  void initState() {
    super.initState();
    _ticker = Timer.periodic(const Duration(milliseconds: 250), (_) => _tick());
  }

  void _tick() {
    if (!mounted) return;
    final now = DateTime.now();
    final remaining = widget.rest.remaining(now);
    final seconds = remaining.inSeconds;
    if (seconds <= 3 && seconds > 0 && seconds != _lastTickSecond) {
      _lastTickSecond = seconds;
      unawaited(ref.read(audioCuesProvider).play(Cue.tick));
      AuHaptics.selection();
    }
    if (widget.rest.isOver(now) && !widget.rest.alerted) {
      unawaited(ref.read(loggerControllerProvider.notifier).onRestFinished());
    }
    setState(() {});
  }

  @override
  void dispose() {
    _ticker?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final now = DateTime.now();
    final remaining = widget.rest.remaining(now);
    final over = widget.rest.isOver(now);
    final controller = ref.read(loggerControllerProvider.notifier);
    return Material(
      color: au.surfaceRaised,
      elevation: 0,
      child: Container(
        decoration: BoxDecoration(
          border: Border(top: BorderSide(color: over ? au.success : au.accent.withValues(alpha: 0.5))),
          boxShadow: au.elevation3,
        ),
        child: SafeArea(
          top: false,
          child: Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s4, AuSpacing.s3, AuSpacing.s4, AuSpacing.s3),
            child: Row(
              children: [
                SizedBox.square(
                  dimension: 56,
                  child: CircularProgressIndicator(
                    value: widget.rest.fraction(now),
                    strokeWidth: 4,
                    color: over ? au.success : au.accent,
                    backgroundColor: au.border,
                  ),
                ),
                const SizedBox(width: AuSpacing.s3),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text((over ? l10n.restOverTitle : l10n.rest).toUpperCase(),
                          style: au.text.overline.copyWith(color: over ? au.success : au.accent)),
                      Semantics(
                        liveRegion: true,
                        label: l10n.restRemaining(remaining.inSeconds),
                        excludeSemantics: true,
                        child: Directionality(
                          textDirection: TextDirection.ltr,
                          child: Text(formatClock(remaining), style: au.text.numeric.copyWith(fontSize: 30)),
                        ),
                      ),
                      if (widget.nextLabel != null)
                        Text(widget.nextLabel!, style: au.text.caption.copyWith(color: au.textMuted), maxLines: 1, overflow: TextOverflow.ellipsis),
                    ],
                  ),
                ),
                _RestButton(label: '−15', tooltip: l10n.restMinus15, onTap: () => controller.adjustRest(const Duration(seconds: -15))),
                const SizedBox(width: AuSpacing.s1),
                _RestButton(label: '+15', tooltip: l10n.restPlus15, onTap: () => controller.adjustRest(const Duration(seconds: 15))),
                const SizedBox(width: AuSpacing.s2),
                AuButton(label: over ? context.core.done : l10n.skip, size: AuButtonSize.compact, expand: false, onPressed: controller.skipRest),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _RestButton extends StatelessWidget {
  const _RestButton({required this.label, required this.tooltip, required this.onTap});

  final String label;
  final String tooltip;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Tooltip(
      message: tooltip,
      child: InkWell(
        onTap: () {
          AuHaptics.selection();
          onTap();
        },
        borderRadius: BorderRadius.circular(AuRadius.sm),
        child: Container(
          width: 48,
          height: 44,
          alignment: Alignment.center,
          decoration: BoxDecoration(borderRadius: BorderRadius.circular(AuRadius.sm), border: Border.all(color: au.border)),
          child: Directionality(textDirection: TextDirection.ltr, child: Text(label, style: au.text.label)),
        ),
      ),
    );
  }
}

/// Tabata / EMOM / interval timer with audio + haptic cues.
class IntervalTimerCard extends ConsumerStatefulWidget {
  const IntervalTimerCard({super.key, required this.block, required this.clock});

  final LoggerBlock block;
  final BlockClock? clock;

  @override
  ConsumerState<IntervalTimerCard> createState() => _IntervalTimerCardState();
}

class _IntervalTimerCardState extends ConsumerState<IntervalTimerCard> {
  Timer? _ticker;
  int _lastPhase = -1;
  int _lastTick = -1;

  @override
  void initState() {
    super.initState();
    _ticker = Timer.periodic(const Duration(milliseconds: 200), (_) => _tick());
  }

  void _tick() {
    final clock = widget.clock;
    if (!mounted || clock == null || clock.isPaused) return;
    final plan = IntervalPlan.forBlock(widget.block);
    final phase = plan.at(clock.elapsed(DateTime.now()));
    final cues = ref.read(audioCuesProvider);
    if (_lastPhase != -1 && phase.phaseIndex != _lastPhase) {
      if (phase.finished) {
        unawaited(cues.play(Cue.finish));
        AuHaptics.success();
      } else {
        unawaited(cues.play(phase.isWork ? Cue.go : Cue.restOver));
        AuHaptics.heavy();
      }
    } else if (!phase.finished) {
      final s = phase.remaining.inSeconds;
      if (s < 3 && s != _lastTick) {
        _lastTick = s;
        unawaited(cues.play(Cue.tick));
      }
    }
    _lastPhase = phase.phaseIndex;
    setState(() {});
  }

  @override
  void dispose() {
    _ticker?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final controller = ref.read(loggerControllerProvider.notifier);
    final plan = IntervalPlan.forBlock(widget.block);
    final clock = widget.clock;
    final phase = clock == null ? plan.at(Duration.zero) : plan.at(clock.elapsed(DateTime.now()));
    final exercises = widget.block.exercises;
    final currentName = exercises.isEmpty ? '' : exercises[phase.exerciseIndex % exercises.length].exercise.name;
    final color = phase.finished ? au.success : (phase.isWork ? au.accent : au.info);

    return AuCard(
      variant: AuCardVariant.hero,
      child: Column(
        children: [
          Row(
            children: [
              Text(
                phase.finished ? l10n.blockComplete : (phase.isWork ? l10n.work : l10n.rest).toUpperCase(),
                style: au.text.overline.copyWith(color: color),
              ),
              const Spacer(),
              Text(l10n.roundOf(phase.round, phase.totalRounds), style: au.text.label.copyWith(color: au.textSecondary)),
            ],
          ),
          const SizedBox(height: AuSpacing.s3),
          AuTimerDisplay(
            time: clock == null ? phase.duration : phase.remaining,
            total: phase.duration,
            size: 180,
            color: color,
            emphasize: phase.remaining.inSeconds < 3 && clock != null,
          ),
          const SizedBox(height: AuSpacing.s2),
          Text(phase.finished ? '' : currentName, style: au.text.title, textAlign: TextAlign.center),
          Text(
            l10n.totalTime(formatClock(plan.total)),
            style: au.text.caption.copyWith(color: au.textMuted),
          ),
          const SizedBox(height: AuSpacing.s4),
          Row(
            children: [
              if (clock == null)
                Expanded(child: AuButton(label: l10n.startTimer, icon: Icons.play_arrow_rounded, onPressed: () => controller.startClock(widget.block.id)))
              else ...[
                Expanded(
                  child: AuButton.secondary(
                    label: clock.isPaused ? l10n.resume : l10n.pause,
                    icon: clock.isPaused ? Icons.play_arrow_rounded : Icons.pause_rounded,
                    onPressed: () => clock.isPaused ? controller.resumeClock(widget.block.id) : controller.pauseClock(widget.block.id),
                  ),
                ),
                const SizedBox(width: AuSpacing.s3),
                AuIconButton(
                  icon: Icons.replay_rounded,
                  tooltip: l10n.resetTimer,
                  filled: true,
                  onPressed: () => controller.resetClock(widget.block.id),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }
}

/// AMRAP: time-cap countdown plus a big round counter.
class AmrapCard extends ConsumerStatefulWidget {
  const AmrapCard({super.key, required this.block, required this.clock, required this.rounds});

  final LoggerBlock block;
  final BlockClock? clock;
  final int rounds;

  @override
  ConsumerState<AmrapCard> createState() => _AmrapCardState();
}

class _AmrapCardState extends ConsumerState<AmrapCard> {
  Timer? _ticker;
  bool _capAlerted = false;

  @override
  void initState() {
    super.initState();
    _ticker = Timer.periodic(const Duration(milliseconds: 500), (_) {
      if (!mounted) return;
      final clock = widget.clock;
      final cap = widget.block.timeCapSeconds;
      if (clock != null && cap != null && !_capAlerted && clock.elapsed(DateTime.now()).inSeconds >= cap) {
        _capAlerted = true;
        unawaited(ref.read(audioCuesProvider).play(Cue.finish));
        AuHaptics.success();
      }
      setState(() {});
    });
  }

  @override
  void dispose() {
    _ticker?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final controller = ref.read(loggerControllerProvider.notifier);
    final clock = widget.clock;
    final cap = Duration(seconds: widget.block.timeCapSeconds ?? 0);
    final elapsed = clock?.elapsed(DateTime.now()) ?? Duration.zero;
    final remaining = cap > Duration.zero ? (cap - elapsed).isNegative ? Duration.zero : cap - elapsed : elapsed;
    final done = cap > Duration.zero && remaining == Duration.zero && clock != null;

    return AuCard(
      variant: AuCardVariant.hero,
      child: Column(
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text((done ? l10n.timeCapReached : l10n.timeCap).toUpperCase(), style: au.text.overline.copyWith(color: done ? au.success : au.accent)),
                    Directionality(
                      textDirection: TextDirection.ltr,
                      child: Text(formatClock(remaining), style: au.text.numeric.copyWith(fontSize: 40)),
                    ),
                  ],
                ),
              ),
              Column(
                children: [
                  Text(l10n.rounds.toUpperCase(), style: au.text.overline.copyWith(color: au.textMuted)),
                  Row(
                    children: [
                      AuIconButton(
                        icon: Icons.remove_rounded,
                        tooltip: context.core.decrease,
                        filled: true,
                        onPressed: widget.rounds > 0 ? () => controller.changeAmrapRounds(widget.block.id, -1) : null,
                      ),
                      SizedBox(
                        width: 56,
                        child: Text('${widget.rounds}', style: au.text.numeric.copyWith(fontSize: 36), textAlign: TextAlign.center),
                      ),
                      Semantics(
                        button: true,
                        label: l10n.addRound,
                        child: GestureDetector(
                          onTap: () => controller.changeAmrapRounds(widget.block.id, 1),
                          child: Container(
                            width: 56,
                            height: 56,
                            decoration: BoxDecoration(shape: BoxShape.circle, gradient: au.goldGradient, boxShadow: au.glow),
                            child: Icon(Icons.add_rounded, color: au.onAccent, size: 30),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: AuSpacing.s4),
          if (clock == null)
            AuButton(label: l10n.startTimer, icon: Icons.play_arrow_rounded, onPressed: () => controller.startClock(widget.block.id))
          else
            Row(
              children: [
                Expanded(
                  child: AuButton.secondary(
                    label: clock.isPaused ? l10n.resume : l10n.pause,
                    icon: clock.isPaused ? Icons.play_arrow_rounded : Icons.pause_rounded,
                    onPressed: () => clock.isPaused ? controller.resumeClock(widget.block.id) : controller.pauseClock(widget.block.id),
                  ),
                ),
                const SizedBox(width: AuSpacing.s3),
                AuIconButton(icon: Icons.replay_rounded, tooltip: l10n.resetTimer, filled: true, onPressed: () => controller.resetClock(widget.block.id)),
              ],
            ),
        ],
      ),
    );
  }
}
