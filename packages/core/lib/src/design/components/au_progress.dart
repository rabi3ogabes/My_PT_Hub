import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../../models/nutrition.dart';
import '../app_tokens.dart';
import '../au_theme.dart';

/// Animated circular progress ring with rounded caps. Progress above 1 draws
/// a second lap in a deeper tone so overshoot is visible (e.g. kcal over
/// target). Mirrors direction in RTL.
class AuProgressRing extends StatelessWidget {
  const AuProgressRing({
    super.key,
    required this.progress,
    this.size = 96,
    this.strokeWidth = 10,
    this.color,
    this.trackColor,
    this.child,
    this.semanticLabel,
    this.animate = true,
  });

  final double progress;
  final double size;
  final double strokeWidth;
  final Color? color;
  final Color? trackColor;
  final Widget? child;
  final String? semanticLabel;
  final bool animate;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final rtl = Directionality.of(context) == TextDirection.rtl;
    final target = progress.isNaN ? 0.0 : progress.clamp(0.0, 2.0);
    Widget ring(double value) => CustomPaint(
          size: Size.square(size),
          painter: _RingPainter(
            progress: value,
            color: color ?? au.accent,
            trackColor: trackColor ?? (au.isDark ? au.border : au.surfaceSunken),
            strokeWidth: strokeWidth,
            clockwise: !rtl,
          ),
          child: SizedBox.square(dimension: size, child: Center(child: child)),
        );
    return Semantics(
      label: semanticLabel,
      value: '${(target * 100).round()}%',
      child: animate
          ? TweenAnimationBuilder<double>(
              tween: Tween(begin: 0, end: target),
              duration: AuMotion.slow * 2,
              curve: AuMotion.easeOut,
              builder: (_, value, _) => ring(value),
            )
          : ring(target),
    );
  }
}

class _RingPainter extends CustomPainter {
  _RingPainter({
    required this.progress,
    required this.color,
    required this.trackColor,
    required this.strokeWidth,
    required this.clockwise,
  });

  final double progress;
  final Color color;
  final Color trackColor;
  final double strokeWidth;
  final bool clockwise;

  @override
  void paint(Canvas canvas, Size size) {
    final rect = Offset(strokeWidth / 2, strokeWidth / 2) & Size(size.width - strokeWidth, size.height - strokeWidth);
    final track = Paint()
      ..color = trackColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth;
    canvas.drawArc(rect, 0, math.pi * 2, false, track);
    if (progress <= 0) return;
    final dir = clockwise ? 1.0 : -1.0;
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth
      ..strokeCap = StrokeCap.round;
    final first = progress.clamp(0.0, 1.0);
    canvas.drawArc(rect, -math.pi / 2, dir * math.pi * 2 * first, false, paint);
    if (progress > 1) {
      final over = Paint()
        ..color = Color.lerp(color, Colors.black, 0.35)!
        ..style = PaintingStyle.stroke
        ..strokeWidth = strokeWidth
        ..strokeCap = StrokeCap.round;
      canvas.drawArc(rect, -math.pi / 2, dir * math.pi * 2 * (progress - 1).clamp(0.0, 1.0), false, over);
    }
  }

  @override
  bool shouldRepaint(_RingPainter old) =>
      old.progress != progress || old.color != color || old.trackColor != trackColor || old.clockwise != clockwise;
}

/// Concentric protein / carbs / fat rings with kcal in the centre.
class AuMacroRings extends StatelessWidget {
  const AuMacroRings({
    super.key,
    required this.consumed,
    required this.targets,
    this.size = 168,
    this.center,
  });

  final Macros consumed;
  final Macros? targets;
  final double size;
  final Widget? center;

  static double _ratio(double value, double? target) => target == null || target <= 0 ? 0 : value / target;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final stroke = size * 0.075;
    final gap = stroke * 1.35;
    return SizedBox.square(
      dimension: size,
      child: Stack(
        alignment: Alignment.center,
        children: [
          AuProgressRing(progress: _ratio(consumed.kcal, targets?.kcal), size: size, strokeWidth: stroke, color: au.accent),
          AuProgressRing(
            progress: _ratio(consumed.proteinG, targets?.proteinG),
            size: size - gap * 2,
            strokeWidth: stroke * 0.8,
            color: au.protein,
          ),
          AuProgressRing(
            progress: _ratio(consumed.carbsG, targets?.carbsG),
            size: size - gap * 3.6,
            strokeWidth: stroke * 0.8,
            color: au.carbs.withValues(alpha: 0.85),
          ),
          AuProgressRing(
            progress: _ratio(consumed.fatG, targets?.fatG),
            size: size - gap * 5.2,
            strokeWidth: stroke * 0.8,
            color: au.fat,
          ),
          ?center,
        ],
      ),
    );
  }
}

/// Horizontal macro bar with label and value.
class AuMacroBar extends StatelessWidget {
  const AuMacroBar({
    super.key,
    required this.label,
    required this.value,
    required this.target,
    required this.color,
    required this.valueLabel,
  });

  final String label;
  final double value;
  final double? target;
  final Color color;
  final String valueLabel;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final ratio = target == null || target! <= 0 ? 0.0 : (value / target!).clamp(0.0, 1.0);
    return Semantics(
      label: '$label $valueLabel',
      excludeSemantics: true,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
              const SizedBox(width: AuSpacing.s2),
              Expanded(child: Text(label, style: au.text.label.copyWith(color: au.textSecondary))),
              Text(valueLabel, style: au.text.label),
            ],
          ),
          const SizedBox(height: AuSpacing.s2),
          ClipRRect(
            borderRadius: BorderRadius.circular(AuRadius.pill),
            child: TweenAnimationBuilder<double>(
              tween: Tween(begin: 0, end: ratio),
              duration: AuMotion.slow * 2,
              curve: AuMotion.easeOut,
              builder: (_, v, _) => LinearProgressIndicator(
                value: v,
                minHeight: 6,
                color: color,
                backgroundColor: au.isDark ? au.border : au.surfaceSunken,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
