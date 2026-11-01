import 'dart:async';

import 'package:flutter/material.dart';

import '../../l10n/gen/au_localizations.dart';
import '../app_tokens.dart';
import '../au_theme.dart';
import '../haptics.dart';

/// Segmented control with a sliding gold-tinted thumb (RTL-aware).
class AuSegmented<T> extends StatelessWidget {
  const AuSegmented({super.key, required this.segments, required this.selected, required this.onChanged});

  final Map<T, String> segments;
  final T selected;
  final ValueChanged<T> onChanged;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final keys = segments.keys.toList();
    final index = keys.indexOf(selected).clamp(0, keys.length - 1);
    return Container(
      height: kAuTouchTarget,
      padding: const EdgeInsets.all(3),
      decoration: BoxDecoration(color: au.surfaceSunken, borderRadius: BorderRadius.circular(AuRadius.md)),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final width = constraints.maxWidth / keys.length;
          return Stack(
            children: [
              AnimatedPositionedDirectional(
                duration: AuMotion.base,
                curve: AuMotion.easeOut,
                start: width * index,
                top: 0,
                bottom: 0,
                width: width,
                child: Container(
                  decoration: BoxDecoration(
                    color: au.surfaceRaised,
                    borderRadius: BorderRadius.circular(AuRadius.sm),
                    border: Border.all(color: au.accent.withValues(alpha: 0.4)),
                    boxShadow: au.elevation1,
                  ),
                ),
              ),
              Row(
                children: [
                  for (final key in keys)
                    Expanded(
                      child: Semantics(
                        selected: key == selected,
                        button: true,
                        child: GestureDetector(
                          behavior: HitTestBehavior.opaque,
                          onTap: () {
                            if (key == selected) return;
                            AuHaptics.selection();
                            onChanged(key);
                          },
                          child: Center(
                            child: AnimatedDefaultTextStyle(
                              duration: AuMotion.fast,
                              style: au.text.label.copyWith(color: key == selected ? au.textPrimary : au.textMuted),
                              child: Text(segments[key]!, maxLines: 1, overflow: TextOverflow.ellipsis),
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ],
          );
        },
      ),
    );
  }
}

/// − value + stepper. Long-press repeats. 44pt buttons.
class AuStepper extends StatefulWidget {
  const AuStepper({
    super.key,
    required this.value,
    required this.onChanged,
    this.step = 1,
    this.min = 0,
    this.max = 9999,
    this.format,
    this.unit,
    this.label,
  });

  final double value;
  final ValueChanged<double> onChanged;
  final double step;
  final double min;
  final double max;
  final String Function(double)? format;
  final String? unit;
  final String? label;

  @override
  State<AuStepper> createState() => _AuStepperState();
}

class _AuStepperState extends State<AuStepper> {
  Timer? _repeat;

  void _change(double delta) {
    final next = (widget.value + delta).clamp(widget.min, widget.max).toDouble();
    if (next == widget.value) return;
    AuHaptics.selection();
    widget.onChanged(double.parse(next.toStringAsFixed(3)));
  }

  void _startRepeat(double delta) {
    _repeat?.cancel();
    _repeat = Timer.periodic(const Duration(milliseconds: 110), (_) => _change(delta));
  }

  void _stopRepeat() {
    _repeat?.cancel();
    _repeat = null;
  }

  @override
  void dispose() {
    _stopRepeat();
    super.dispose();
  }

  String _display(double v) {
    if (widget.format != null) return widget.format!(v);
    return v == v.roundToDouble() ? v.toStringAsFixed(0) : v.toStringAsFixed(1);
  }

  Widget _button(IconData icon, double delta, String tooltip) {
    final au = context.au;
    return Tooltip(
      message: tooltip,
      child: GestureDetector(
        onTap: () => _change(delta),
        onLongPressStart: (_) => _startRepeat(delta),
        onLongPressEnd: (_) => _stopRepeat(),
        child: Container(
          width: kAuTouchTarget,
          height: kAuTouchTarget,
          decoration: BoxDecoration(color: au.surfaceRaised, shape: BoxShape.circle, border: Border.all(color: au.border)),
          child: Icon(icon, color: au.textPrimary, size: 20),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = AuL10n.of(context);
    return Semantics(
      label: widget.label,
      value: '${_display(widget.value)} ${widget.unit ?? ''}',
      increasedValue: _display((widget.value + widget.step).clamp(widget.min, widget.max)),
      decreasedValue: _display((widget.value - widget.step).clamp(widget.min, widget.max)),
      onIncrease: () => _change(widget.step),
      onDecrease: () => _change(-widget.step),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          ExcludeSemantics(child: _button(Icons.remove_rounded, -widget.step, l10n.decrease)),
          ConstrainedBox(
            constraints: const BoxConstraints(minWidth: 72),
            child: ExcludeSemantics(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(_display(widget.value), style: au.text.numeric.copyWith(fontSize: 24), textAlign: TextAlign.center),
                  if (widget.unit != null) Text(widget.unit!, style: au.text.caption.copyWith(color: au.textMuted)),
                ],
              ),
            ),
          ),
          ExcludeSemantics(child: _button(Icons.add_rounded, widget.step, l10n.increase)),
        ],
      ),
    );
  }
}

/// Formats a duration as `m:ss` (or `h:mm:ss`).
String formatClock(Duration d) {
  final negative = d.isNegative;
  final abs = d.abs();
  final h = abs.inHours;
  final m = abs.inMinutes.remainder(60);
  final s = abs.inSeconds.remainder(60);
  final body = h > 0
      ? '$h:${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}'
      : '$m:${s.toString().padLeft(2, '0')}';
  return negative ? '-$body' : body;
}

/// Big tabular timer with an optional progress ring. Digits always render
/// LTR (time reads the same in Arabic).
class AuTimerDisplay extends StatelessWidget {
  const AuTimerDisplay({
    super.key,
    required this.time,
    this.total,
    this.label,
    this.size = 200,
    this.color,
    this.emphasize = false,
  });

  final Duration time;
  final Duration? total;
  final String? label;
  final double size;
  final Color? color;

  /// Pulses the accent (last seconds of a countdown).
  final bool emphasize;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final tone = color ?? au.accent;
    final progress = total == null || total!.inMilliseconds == 0
        ? null
        : (time.inMilliseconds / total!.inMilliseconds).clamp(0.0, 1.0);
    final text = Directionality(
      textDirection: TextDirection.ltr,
      child: Text(
        formatClock(time),
        style: au.text.numeric.copyWith(
          fontSize: size * 0.24,
          fontWeight: FontWeight.w800,
          color: emphasize ? tone : au.textPrimary,
        ),
      ),
    );
    final column = Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        if (label != null) Text(label!.toUpperCase(), style: au.text.overline.copyWith(color: au.textMuted)),
        text,
      ],
    );
    return Semantics(
      liveRegion: false,
      label: '${label ?? ''} ${formatClock(time)}',
      excludeSemantics: true,
      child: SizedBox.square(
        dimension: size,
        child: progress == null
            ? Center(child: column)
            : CustomPaint(
                painter: _CountdownPainter(progress: progress, color: tone, track: au.surfaceSunken),
                child: Center(child: column),
              ),
      ),
    );
  }
}

class _CountdownPainter extends CustomPainter {
  _CountdownPainter({required this.progress, required this.color, required this.track});

  final double progress;
  final Color color;
  final Color track;

  @override
  void paint(Canvas canvas, Size size) {
    const stroke = 6.0;
    final rect = const Offset(stroke / 2, stroke / 2) & Size(size.width - stroke, size.height - stroke);
    canvas.drawArc(rect, 0, 6.283185307, false, Paint()
      ..color = track
      ..style = PaintingStyle.stroke
      ..strokeWidth = stroke);
    canvas.drawArc(rect, -1.5707963, 6.283185307 * progress, false, Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round
      ..strokeWidth = stroke);
  }

  @override
  bool shouldRepaint(_CountdownPainter old) => old.progress != progress || old.color != color;
}
