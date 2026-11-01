import 'dart:math' as math;

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import 'splash_screen.dart';

/// Three-slide welcome carousel.
class OnboardingScreen extends ConsumerStatefulWidget {
  const OnboardingScreen({super.key});

  @override
  ConsumerState<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends ConsumerState<OnboardingScreen> {
  final _controller = PageController();
  int _page = 0;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _finish(String route) async {
    await ref.read(appPrefsProvider).setOnboardingSeen();
    if (mounted) context.go(route);
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final slides = [
      (l10n.onboardingTitle1, l10n.onboardingBody1, _SlideArt.rings),
      (l10n.onboardingTitle2, l10n.onboardingBody2, _SlideArt.bars),
      (l10n.onboardingTitle3, l10n.onboardingBody3, _SlideArt.orbit),
    ];
    final last = _page == slides.length - 1;

    return Scaffold(
      body: DecoratedBox(
        decoration: BoxDecoration(
          gradient: RadialGradient(
            center: const AlignmentDirectional(0.6, -0.7).resolve(Directionality.of(context)),
            radius: 1.2,
            colors: [au.accent.withValues(alpha: au.isDark ? 0.16 : 0.10), au.background],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s3, AuSpacing.s3, 0),
                child: Row(
                  children: [
                    const AurumMark(size: 32),
                    const SizedBox(width: AuSpacing.s3),
                    Text(context.core.brandName.toUpperCase(), style: au.text.overline.copyWith(color: au.textPrimary, fontSize: 13)),
                    const Spacer(),
                    IconButton(
                      tooltip: l10n.chooseLanguage,
                      icon: const Icon(Icons.translate_rounded),
                      onPressed: () => context.push(Routes.language),
                    ),
                    if (!last) TextButton(onPressed: () => _finish(Routes.signIn), child: Text(context.core.skip)),
                  ],
                ),
              ),
              Expanded(
                child: PageView.builder(
                  controller: _controller,
                  itemCount: slides.length,
                  onPageChanged: (p) {
                    AuHaptics.selection();
                    setState(() => _page = p);
                  },
                  itemBuilder: (context, i) {
                    final (title, body, art) = slides[i];
                    return Padding(
                      padding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s6),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(child: Center(child: _Art(art: art, active: i == _page))),
                          Text('0${i + 1}', style: au.text.overline.copyWith(color: au.accent)),
                          const SizedBox(height: AuSpacing.s2),
                          Text(title, style: au.text.h1),
                          const SizedBox(height: AuSpacing.s3),
                          Text(body, style: au.text.body.copyWith(color: au.textSecondary)),
                          const SizedBox(height: AuSpacing.s8),
                        ],
                      ),
                    );
                  },
                ),
              ),
              Padding(
                padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s6, 0, AuSpacing.s6, AuSpacing.s6),
                child: Column(
                  children: [
                    Row(
                      children: [
                        for (var i = 0; i < slides.length; i++)
                          AnimatedContainer(
                            duration: AuMotion.base,
                            curve: AuMotion.easeOut,
                            margin: const EdgeInsetsDirectional.only(end: AuSpacing.s2),
                            width: i == _page ? 28 : 8,
                            height: 8,
                            decoration: BoxDecoration(
                              color: i == _page ? au.accent : au.borderStrong,
                              borderRadius: BorderRadius.circular(AuRadius.pill),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: AuSpacing.s6),
                    AuButton(
                      label: last ? l10n.getStarted : context.core.next,
                      trailingIcon: last ? null : Icons.arrow_forward_rounded,
                      onPressed: () {
                        if (last) {
                          _finish(Routes.signUp);
                        } else {
                          _controller.nextPage(duration: AuMotion.slow, curve: AuMotion.easeOut);
                        }
                      },
                    ),
                    const SizedBox(height: AuSpacing.s2),
                    AuButton.ghost(label: l10n.haveAccountSignIn, onPressed: () => _finish(Routes.signIn)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

enum _SlideArt { rings, bars, orbit }

class _Art extends StatelessWidget {
  const _Art({required this.art, required this.active});

  final _SlideArt art;
  final bool active;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return TweenAnimationBuilder<double>(
      tween: Tween(begin: 0, end: active ? 1 : 0.4),
      duration: const Duration(milliseconds: 900),
      curve: AuMotion.easeOut,
      builder: (_, t, _) => SizedBox.square(
        dimension: 260,
        child: CustomPaint(painter: _ArtPainter(art: art, t: t, gold: au.accent, light: au.accentLight, track: au.border)),
      ),
    );
  }
}

class _ArtPainter extends CustomPainter {
  _ArtPainter({required this.art, required this.t, required this.gold, required this.light, required this.track});

  final _SlideArt art;
  final double t;
  final Color gold;
  final Color light;
  final Color track;

  @override
  void paint(Canvas canvas, Size size) {
    final c = size.center(Offset.zero);
    final r = size.width / 2;
    switch (art) {
      case _SlideArt.rings:
        for (var i = 0; i < 4; i++) {
          final radius = r * (0.95 - i * 0.2);
          final rect = Rect.fromCircle(center: c, radius: radius);
          canvas.drawArc(rect, 0, math.pi * 2, false, Paint()
            ..color = track
            ..style = PaintingStyle.stroke
            ..strokeWidth = 10);
          canvas.drawArc(rect, -math.pi / 2, math.pi * 2 * t * (0.9 - i * 0.15), false, Paint()
            ..shader = SweepGradient(colors: [light, gold, light]).createShader(rect)
            ..style = PaintingStyle.stroke
            ..strokeCap = StrokeCap.round
            ..strokeWidth = 10);
        }
      case _SlideArt.bars:
        const heights = [0.35, 0.5, 0.45, 0.7, 0.62, 0.9];
        final w = size.width / (heights.length * 1.8);
        for (var i = 0; i < heights.length; i++) {
          final h = size.height * 0.8 * heights[i] * t;
          final x = size.width * 0.1 + i * w * 1.8;
          final rect = RRect.fromRectAndRadius(Rect.fromLTWH(x, size.height * 0.9 - h, w, h), Radius.circular(w / 2));
          canvas.drawRRect(rect, Paint()
            ..shader = LinearGradient(begin: Alignment.bottomCenter, end: Alignment.topCenter, colors: [gold.withValues(alpha: 0.35), light])
                .createShader(rect.outerRect));
        }
        final path = Path();
        for (var i = 0; i < heights.length; i++) {
          final x = size.width * 0.1 + i * w * 1.8 + w / 2;
          final y = size.height * 0.9 - size.height * 0.8 * heights[i] * t - 18;
          i == 0 ? path.moveTo(x, y) : path.lineTo(x, y);
        }
        canvas.drawPath(path, Paint()
          ..color = gold
          ..style = PaintingStyle.stroke
          ..strokeWidth = 2.5
          ..strokeJoin = StrokeJoin.round);
      case _SlideArt.orbit:
        canvas.drawCircle(c, r * 0.9, Paint()
          ..color = track
          ..style = PaintingStyle.stroke
          ..strokeWidth = 1.5);
        canvas.drawCircle(c, r * 0.6, Paint()
          ..color = track
          ..style = PaintingStyle.stroke
          ..strokeWidth = 1.5);
        canvas.drawCircle(c, r * 0.28, Paint()
          ..shader = RadialGradient(colors: [light, gold]).createShader(Rect.fromCircle(center: c, radius: r * 0.28)));
        for (var i = 0; i < 3; i++) {
          final angle = t * math.pi * 1.2 + i * math.pi * 2 / 3;
          final orbit = i.isEven ? r * 0.9 : r * 0.6;
          final p = c + Offset(math.cos(angle), math.sin(angle)) * orbit;
          canvas.drawCircle(p, 10 - i * 2.0, Paint()..color = i == 0 ? gold : light);
        }
    }
  }

  @override
  bool shouldRepaint(_ArtPainter old) => old.t != t || old.gold != gold;
}
