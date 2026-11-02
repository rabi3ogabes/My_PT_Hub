import 'dart:async';
import 'dart:io';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';
import 'package:record/record.dart';

import '../../shared/l10n_ext.dart';

/// Hold-to-record voice note button with slide-to-cancel.
///
/// Recording starts on long-press, the elapsed time and a "slide to cancel"
/// hint appear; sliding the finger towards the start edge beyond
/// [cancelDistance] discards the note; releasing sends it.
class VoiceRecorderButton extends StatefulWidget {
  const VoiceRecorderButton({super.key, required this.onRecorded, this.cancelDistance = 110});

  final Future<void> Function(String path, int durationMs) onRecorded;
  final double cancelDistance;

  @override
  State<VoiceRecorderButton> createState() => _VoiceRecorderButtonState();
}

class _VoiceRecorderButtonState extends State<VoiceRecorderButton> {
  final _recorder = AudioRecorder();
  DateTime? _startedAt;
  Timer? _ticker;
  double _drag = 0;
  bool _recording = false;
  bool _cancelling = false;

  @override
  void dispose() {
    _ticker?.cancel();
    _recorder.dispose();
    super.dispose();
  }

  Future<void> _start() async {
    if (!await _recorder.hasPermission()) {
      if (mounted) AuToast.show(context, context.l10n.microphonePermission, tone: AuToastTone.warning);
      return;
    }
    final dir = await getTemporaryDirectory();
    final path = p.join(dir.path, 'voice_${Ulid.generate()}.m4a');
    await _recorder.start(const RecordConfig(encoder: AudioEncoder.aacLc, bitRate: 64000, sampleRate: 44100), path: path);
    AuHaptics.medium();
    setState(() {
      _recording = true;
      _cancelling = false;
      _drag = 0;
      _startedAt = DateTime.now();
    });
    _ticker = Timer.periodic(const Duration(milliseconds: 200), (_) {
      if (mounted) setState(() {});
    });
  }

  Future<void> _stop({required bool send}) async {
    _ticker?.cancel();
    final path = await _recorder.stop();
    final started = _startedAt;
    setState(() {
      _recording = false;
      _cancelling = false;
      _drag = 0;
      _startedAt = null;
    });
    if (path == null || started == null) return;
    final duration = DateTime.now().difference(started).inMilliseconds;
    if (!send || duration < 700) {
      try {
        File(path).deleteSync();
      } on Object {
        // Temporary file; the OS reclaims it.
      }
      if (!send) AuHaptics.light();
      return;
    }
    AuHaptics.success();
    await widget.onRecorded(path, duration);
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final rtl = Directionality.of(context) == TextDirection.rtl;
    final elapsed = _startedAt == null ? Duration.zero : DateTime.now().difference(_startedAt!);
    final progress = (_drag.abs() / widget.cancelDistance).clamp(0.0, 1.0);

    return Semantics(
      button: true,
      label: l10n.holdToRecord,
      child: GestureDetector(
        onLongPressStart: (_) => _start(),
        onLongPressMoveUpdate: (d) {
          if (!_recording) return;
          final dx = d.offsetFromOrigin.dx;
          // Towards the start edge: left in LTR, right in RTL.
          final towardsStart = rtl ? dx > 0 : dx < 0;
          setState(() {
            _drag = towardsStart ? dx : 0;
            final cancelling = _drag.abs() >= widget.cancelDistance;
            if (cancelling && !_cancelling) AuHaptics.heavy();
            _cancelling = cancelling;
          });
        },
        onLongPressEnd: (_) {
          if (_recording) _stop(send: !_cancelling);
        },
        onLongPressCancel: () {
          if (_recording) _stop(send: false);
        },
        child: Stack(
          clipBehavior: Clip.none,
          alignment: AlignmentDirectional.centerEnd,
          children: [
            if (_recording)
              PositionedDirectional(
                end: 56,
                child: Opacity(
                  opacity: 1 - progress,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(width: 10, height: 10, decoration: BoxDecoration(color: au.danger, shape: BoxShape.circle)),
                      const SizedBox(width: AuSpacing.s2),
                      Text(formatClock(elapsed), style: au.text.label.copyWith(fontFeatures: const [FontFeature.tabularFigures()])),
                      const SizedBox(width: AuSpacing.s4),
                      AuDirectionalIcon(Icons.chevron_left_rounded, size: 18, color: au.textMuted),
                      Text(l10n.slideToCancel, style: au.text.caption.copyWith(color: au.textMuted)),
                    ],
                  ),
                ),
              ),
            AnimatedContainer(
              duration: AuMotion.fast,
              width: _recording ? 60 : kAuTouchTarget,
              height: _recording ? 60 : kAuTouchTarget,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: _cancelling ? null : au.goldGradient,
                color: _cancelling ? au.danger : null,
                boxShadow: _recording ? au.glow : null,
              ),
              child: Icon(_cancelling ? Icons.delete_outline_rounded : Icons.mic_rounded, color: _cancelling ? Colors.white : au.onAccent),
            ),
          ],
        ),
      ),
    );
  }
}
