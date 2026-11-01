import 'dart:math' as math;

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'logger_controller.dart';

/// Finish summary: duration, volume, sets, effort rating and notes; after
/// saving, PRs are revealed with a gold celebration.
class FinishScreen extends ConsumerStatefulWidget {
  const FinishScreen({super.key});

  @override
  ConsumerState<FinishScreen> createState() => _FinishScreenState();
}

class _FinishScreenState extends ConsumerState<FinishScreen> {
  final _notes = TextEditingController();
  int? _effort;
  bool _saving = false;
  FinishResult? _result;

  @override
  void dispose() {
    _notes.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    try {
      final result = await ref.read(loggerControllerProvider.notifier).finish(
            perceivedEffort: _effort,
            notes: _notes.text.trim().isEmpty ? null : _notes.text.trim(),
          );
      ref.read(lastFinishResultProvider.notifier).set(result);
      if (!mounted) return;
      AuHaptics.success();
      setState(() => _result = result);
    } on Object catch (e) {
      if (mounted) showError(context, e);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final au = context.au;
    final units = ref.watch(unitsProvider);
    final result = _result;
    final summary = result?.summary ?? ref.read(loggerControllerProvider.notifier).summary();

    if (summary == null) {
      return Scaffold(
        appBar: AppBar(),
        body: AuEmptyState(
          icon: Icons.fitness_center_rounded,
          title: l10n.noActiveWorkout,
          message: l10n.noActiveWorkoutHint,
          actionLabel: l10n.goToTrain,
          onAction: () => context.go(Routes.train),
        ),
      );
    }

    final stats = Row(
      children: [
        Expanded(child: AuStatTile(label: context.core.duration, value: formatClock(summary.duration), icon: Icons.timer_outlined)),
        const SizedBox(width: AuSpacing.s3),
        Expanded(
          child: AuStatTile(
            label: l10n.volume,
            value: context.formatNumber(Units.weightToDisplay(summary.volumeKg, units), maxDecimals: 0),
            unit: weightUnitLabel(context, units),
            icon: Icons.stacked_bar_chart_rounded,
          ),
        ),
      ],
    );

    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: result == null,
        title: Text(result == null ? l10n.finishWorkout : l10n.workoutComplete),
      ),
      body: Stack(
        children: [
          ListView(
            padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s2, AuSpacing.s5, AuSpacing.s16),
            children: [
              if (result != null) ...[
                _Hero(title: l10n.greatWork, subtitle: summary.name ?? ''),
                const SizedBox(height: AuSpacing.s6),
              ],
              stats,
              const SizedBox(height: AuSpacing.s3),
              Row(
                children: [
                  Expanded(child: AuStatTile(label: l10n.setsLabel, value: '${summary.completedSets}/${summary.totalSets}', icon: Icons.check_circle_outline_rounded)),
                  const SizedBox(width: AuSpacing.s3),
                  Expanded(child: AuStatTile(label: l10n.exercisesLabel, value: '${summary.exercises}', icon: Icons.fitness_center_rounded)),
                ],
              ),
              const SizedBox(height: AuSpacing.s6),
              if (result == null) ...[
                AuSectionHeader(title: l10n.howHardWasIt),
                Wrap(
                  spacing: AuSpacing.s2,
                  runSpacing: AuSpacing.s2,
                  children: [
                    for (var i = 1; i <= 10; i++)
                      AuChip(label: '$i', selected: _effort == i, onSelected: (_) => setState(() => _effort = i)),
                  ],
                ),
                if (_effort != null)
                  Padding(
                    padding: const EdgeInsetsDirectional.only(top: AuSpacing.s2),
                    child: Text(_effortLabel(context, _effort!), style: au.text.caption.copyWith(color: au.textSecondary)),
                  ),
                const SizedBox(height: AuSpacing.s6),
                AuTextField(controller: _notes, label: l10n.notesForCoach, hint: l10n.notesForCoachHint, maxLines: 4, minLines: 2),
                const SizedBox(height: AuSpacing.s8),
                AuButton(label: l10n.saveWorkout, icon: Icons.check_rounded, loading: _saving, onPressed: _save),
                const SizedBox(height: AuSpacing.s2),
                AuButton.ghost(label: l10n.keepTraining, expand: true, onPressed: () => context.pop()),
              ] else ...[
                AuSectionHeader(title: l10n.personalRecords),
                if (result.server == null)
                  AuCard(
                    variant: AuCardVariant.sunken,
                    child: Row(
                      children: [
                        Icon(Icons.cloud_upload_outlined, color: au.accent),
                        const SizedBox(width: AuSpacing.s3),
                        Expanded(child: Text(l10n.prsAfterSync, style: au.text.bodySmall.copyWith(color: au.textSecondary))),
                      ],
                    ),
                  )
                else if (result.records.isEmpty)
                  AuCard(
                    variant: AuCardVariant.sunken,
                    child: Text(l10n.noPrsThisTime, style: au.text.bodySmall.copyWith(color: au.textSecondary)),
                  )
                else
                  for (final pr in result.records) ...[
                    _PrTile(record: pr),
                    const SizedBox(height: AuSpacing.s3),
                  ],
                const SizedBox(height: AuSpacing.s8),
                AuButton(label: context.core.done, onPressed: () => context.go(Routes.today)),
                const SizedBox(height: AuSpacing.s2),
                AuButton.ghost(label: l10n.viewHistory, expand: true, onPressed: () => context.go('${Routes.train}/history')),
              ],
            ],
          ),
          if (result != null && result.records.isNotEmpty) const Positioned.fill(child: IgnorePointer(child: GoldCelebration())),
        ],
      ),
    );
  }

  String _effortLabel(BuildContext context, int effort) {
    final l10n = context.l10n;
    if (effort >= 10) return l10n.rpe10;
    if (effort >= 9) return l10n.rpe9;
    if (effort >= 8) return l10n.rpe8;
    if (effort >= 7) return l10n.rpe7;
    if (effort >= 5) return l10n.rpe6;
    return l10n.effortEasy;
  }
}

class _Hero extends StatelessWidget {
  const _Hero({required this.title, required this.subtitle});

  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return AuCard(
      variant: AuCardVariant.hero,
      padding: const EdgeInsetsDirectional.all(AuSpacing.s6),
      child: Column(
        children: [
          Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(shape: BoxShape.circle, gradient: au.goldGradient, boxShadow: au.glow),
            child: Icon(Icons.emoji_events_rounded, color: au.onAccent, size: 36),
          ),
          const SizedBox(height: AuSpacing.s4),
          Text(title, style: au.text.h2, textAlign: TextAlign.center),
          const SizedBox(height: AuSpacing.s1),
          Text(subtitle, style: au.text.body.copyWith(color: au.textSecondary), textAlign: TextAlign.center),
        ],
      ),
    );
  }
}

String prTypeLabel(BuildContext context, String type) => switch (type) {
      'e1rm' => context.l10n.prE1rm,
      'weight' => context.l10n.prWeight,
      'reps' => context.l10n.prReps,
      'volume' => context.l10n.prVolume,
      _ => type,
    };

class _PrTile extends ConsumerWidget {
  const _PrTile({required this.record});

  final PersonalRecord record;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final units = ref.watch(unitsProvider);
    final isWeight = record.type != 'reps';
    String fmt(double v) => isWeight ? displayWeight(context, v, units) : context.formatNumber(v, maxDecimals: 0);
    return TweenAnimationBuilder<double>(
      tween: Tween(begin: 0, end: 1),
      duration: const Duration(milliseconds: 600),
      curve: AuMotion.easeOut,
      builder: (_, t, child) => Opacity(opacity: t, child: Transform.translate(offset: Offset(0, 20 * (1 - t)), child: child)),
      child: AuCard(
        variant: AuCardVariant.hero,
        child: Row(
          children: [
            Container(
              padding: const EdgeInsetsDirectional.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(gradient: au.goldGradient, borderRadius: BorderRadius.circular(AuRadius.xs)),
              child: Text('PR', style: au.text.label.copyWith(color: au.onAccent, fontWeight: FontWeight.w800)),
            ),
            const SizedBox(width: AuSpacing.s3),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(record.exerciseName, style: au.text.title),
                  Text(prTypeLabel(context, record.type), style: au.text.caption.copyWith(color: au.textMuted)),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(fmt(record.value), style: au.text.title.copyWith(color: au.accent)),
                if (record.previousValue != null)
                  Text(fmt(record.previousValue!), style: au.text.caption.copyWith(color: au.textMuted, decoration: TextDecoration.lineThrough)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

/// Gold particle burst for PR moments (respects reduce-motion).
class GoldCelebration extends StatefulWidget {
  const GoldCelebration({super.key});

  @override
  State<GoldCelebration> createState() => _GoldCelebrationState();
}

class _GoldCelebrationState extends State<GoldCelebration> with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 2600));
  final _particles = List.generate(70, (i) => _Particle(math.Random(i)));

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final reduce = MediaQuery.maybeDisableAnimationsOf(context) ?? false;
    if (!reduce && !_controller.isAnimating && _controller.value == 0) _controller.forward();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return AnimatedBuilder(
      animation: _controller,
      builder: (_, _) => CustomPaint(
        painter: _CelebrationPainter(
          t: _controller.value,
          particles: _particles,
          colors: [au.accent, au.accentLight, au.accentDark, Colors.white],
        ),
      ),
    );
  }
}

class _Particle {
  _Particle(math.Random r)
      : angle = -math.pi / 2 + (r.nextDouble() - 0.5) * math.pi * 1.4,
        speed = 0.35 + r.nextDouble() * 0.65,
        size = 3 + r.nextDouble() * 5,
        spin = r.nextDouble() * math.pi * 2,
        color = r.nextInt(4),
        delay = r.nextDouble() * 0.15;

  final double angle;
  final double speed;
  final double size;
  final double spin;
  final int color;
  final double delay;
}

class _CelebrationPainter extends CustomPainter {
  _CelebrationPainter({required this.t, required this.particles, required this.colors});

  final double t;
  final List<_Particle> particles;
  final List<Color> colors;

  @override
  void paint(Canvas canvas, Size size) {
    if (t == 0 || t == 1) return;
    final origin = Offset(size.width / 2, size.height * 0.28);
    for (final p in particles) {
      final local = ((t - p.delay) / (1 - p.delay)).clamp(0.0, 1.0);
      if (local == 0) continue;
      final distance = size.width * 0.7 * p.speed * Curves.easeOutCubic.transform(local);
      final gravity = size.height * 0.5 * local * local;
      final pos = origin + Offset(math.cos(p.angle) * distance, math.sin(p.angle) * distance + gravity);
      final paint = Paint()..color = colors[p.color].withValues(alpha: (1 - local).clamp(0.0, 1.0));
      canvas.save();
      canvas.translate(pos.dx, pos.dy);
      canvas.rotate(p.spin + local * 8);
      canvas.drawRRect(
        RRect.fromRectAndRadius(Rect.fromCenter(center: Offset.zero, width: p.size, height: p.size * 1.8), const Radius.circular(1.5)),
        paint,
      );
      canvas.restore();
    }
  }

  @override
  bool shouldRepaint(_CelebrationPainter old) => old.t != t;
}
