import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';

import '../../shared/l10n_ext.dart';

/// Shown while the session is restored. Matches the native launch screen
/// (same background) so the hand-off is seamless.
class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Scaffold(
      backgroundColor: au.isDark ? AuDarkColors.background : au.background,
      body: Center(
        child: TweenAnimationBuilder<double>(
          tween: Tween(begin: 0, end: 1),
          duration: const Duration(milliseconds: 700),
          curve: AuMotion.easeOut,
          builder: (_, t, child) => Opacity(opacity: t, child: Transform.scale(scale: 0.94 + 0.06 * t, child: child)),
          child: Semantics(
            label: context.core.brandName,
            child: const AurumMark(size: 88),
          ),
        ),
      ),
    );
  }
}

/// The Aurum monogram: a gold ring with an "A" cut, drawn in code so it is
/// crisp at every size and in both themes.
class AurumMark extends StatelessWidget {
  const AurumMark({super.key, this.size = 64});

  final double size;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return SizedBox.square(
      dimension: size,
      child: DecoratedBox(
        decoration: BoxDecoration(shape: BoxShape.circle, boxShadow: au.glow),
        child: ShaderMask(
          shaderCallback: (rect) => au.goldGradient.createShader(rect),
          child: CustomPaint(painter: _MarkPainter()),
        ),
      ),
    );
  }
}

class _MarkPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final s = size.width;
    final ring = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.stroke
      ..strokeWidth = s * 0.06;
    canvas.drawCircle(Offset(s / 2, s / 2), s * 0.44, ring);
    final a = Path()
      ..moveTo(s * 0.30, s * 0.72)
      ..lineTo(s * 0.50, s * 0.26)
      ..lineTo(s * 0.70, s * 0.72);
    final stroke = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.stroke
      ..strokeWidth = s * 0.07
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    canvas.drawPath(a, stroke);
    canvas.drawLine(Offset(s * 0.38, s * 0.56), Offset(s * 0.62, s * 0.56), stroke..strokeWidth = s * 0.05);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
