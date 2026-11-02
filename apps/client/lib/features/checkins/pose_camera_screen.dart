import 'package:aurum_core/aurum_core.dart';
import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../../shared/l10n_ext.dart';

/// Camera with a ghost silhouette for consistent progress photos. Pops with
/// the captured file path. Falls back to the system picker when no camera is
/// available.
class PoseCameraScreen extends StatefulWidget {
  const PoseCameraScreen({super.key, required this.pose});

  /// `front` | `side` | `back`.
  final String pose;

  @override
  State<PoseCameraScreen> createState() => _PoseCameraScreenState();
}

class _PoseCameraScreenState extends State<PoseCameraScreen> {
  CameraController? _controller;
  List<CameraDescription> _cameras = const [];
  int _index = 0;
  bool _failed = false;
  bool _capturing = false;

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    try {
      _cameras = await availableCameras();
      if (_cameras.isEmpty) throw CameraException('none', 'no camera');
      _index = _cameras.indexWhere((c) => c.lensDirection == CameraLensDirection.back).clamp(0, _cameras.length - 1);
      await _open();
    } on Object {
      if (mounted) setState(() => _failed = true);
    }
  }

  Future<void> _open() async {
    final previous = _controller;
    final controller = CameraController(_cameras[_index], ResolutionPreset.high, enableAudio: false);
    await previous?.dispose();
    await controller.initialize();
    if (!mounted) return;
    setState(() => _controller = controller);
  }

  @override
  void dispose() {
    _controller?.dispose();
    super.dispose();
  }

  Future<void> _capture() async {
    final c = _controller;
    if (c == null || _capturing) return;
    setState(() => _capturing = true);
    try {
      final file = await c.takePicture();
      AuHaptics.medium();
      if (mounted) Navigator.of(context).pop(file.path);
    } on Object {
      if (mounted) setState(() => _capturing = false);
    }
  }

  Future<void> _pickFromLibrary() async {
    final file = await ImagePicker().pickImage(source: ImageSource.gallery, maxWidth: 2000, imageQuality: 90);
    if (file != null && mounted) Navigator.of(context).pop(file.path);
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final c = _controller;
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        foregroundColor: Colors.white,
        title: Text(switch (widget.pose) {
          'side' => l10n.poseSide,
          'back' => l10n.poseBack,
          _ => l10n.poseFront,
        }),
      ),
      extendBodyBehindAppBar: true,
      body: Stack(
        fit: StackFit.expand,
        children: [
          if (c != null && c.value.isInitialized)
            Center(child: CameraPreview(c))
          else if (_failed)
            Center(
              child: Padding(
                padding: const EdgeInsetsDirectional.all(AuSpacing.s8),
                child: Text(l10n.cameraUnavailable, style: au.text.body.copyWith(color: Colors.white), textAlign: TextAlign.center),
              ),
            )
          else
            const Center(child: CircularProgressIndicator()),
          IgnorePointer(child: CustomPaint(painter: PoseGhostPainter(pose: widget.pose, color: au.accentLight))),
          PositionedDirectional(
            start: 0,
            end: 0,
            top: MediaQuery.paddingOf(context).top + 64,
            child: Text(l10n.poseHint, textAlign: TextAlign.center, style: au.text.bodySmall.copyWith(color: Colors.white70)),
          ),
          PositionedDirectional(
            start: 0,
            end: 0,
            bottom: MediaQuery.paddingOf(context).bottom + AuSpacing.s6,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                IconButton(
                  tooltip: l10n.chooseFromLibrary,
                  icon: const Icon(Icons.photo_library_outlined, color: Colors.white),
                  onPressed: _pickFromLibrary,
                ),
                Semantics(
                  button: true,
                  label: l10n.takePhoto,
                  child: GestureDetector(
                    onTap: _capture,
                    child: Container(
                      width: 76,
                      height: 76,
                      decoration: BoxDecoration(shape: BoxShape.circle, border: Border.all(color: Colors.white, width: 4)),
                      padding: const EdgeInsets.all(4),
                      child: DecoratedBox(decoration: BoxDecoration(shape: BoxShape.circle, gradient: au.goldGradient)),
                    ),
                  ),
                ),
                IconButton(
                  tooltip: l10n.switchCamera,
                  icon: const Icon(Icons.cameraswitch_outlined, color: Colors.white),
                  onPressed: _cameras.length < 2
                      ? null
                      : () {
                          _index = (_index + 1) % _cameras.length;
                          _open();
                        },
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Dashed human silhouette (front/back or side profile).
class PoseGhostPainter extends CustomPainter {
  PoseGhostPainter({required this.pose, required this.color});

  final String pose;
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color.withValues(alpha: 0.6)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.5
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    final cx = size.width / 2;
    final top = size.height * 0.16;
    final h = size.height * 0.70;
    final head = h * 0.065;
    canvas.drawCircle(Offset(cx, top + head), head, paint);
    final path = Path();
    if (pose == 'side') {
      path
        ..moveTo(cx, top + head * 2)
        ..quadraticBezierTo(cx + h * 0.08, top + h * 0.22, cx + h * 0.05, top + h * 0.45)
        ..quadraticBezierTo(cx + h * 0.06, top + h * 0.7, cx + h * 0.02, top + h)
        ..moveTo(cx, top + head * 2)
        ..quadraticBezierTo(cx - h * 0.07, top + h * 0.25, cx - h * 0.05, top + h * 0.47)
        ..quadraticBezierTo(cx - h * 0.04, top + h * 0.72, cx - h * 0.04, top + h)
        ..moveTo(cx, top + h * 0.2)
        ..lineTo(cx + h * 0.02, top + h * 0.48);
    } else {
      final shoulder = h * 0.15;
      final hip = h * 0.11;
      path
        ..moveTo(cx - shoulder, top + h * 0.17)
        ..quadraticBezierTo(cx, top + h * 0.13, cx + shoulder, top + h * 0.17)
        ..lineTo(cx + shoulder * 1.15, top + h * 0.46)
        ..moveTo(cx - shoulder, top + h * 0.17)
        ..lineTo(cx - shoulder * 1.15, top + h * 0.46)
        ..moveTo(cx - shoulder * 0.85, top + h * 0.2)
        ..quadraticBezierTo(cx - hip * 0.9, top + h * 0.36, cx - hip, top + h * 0.5)
        ..lineTo(cx - hip * 0.7, top + h)
        ..moveTo(cx + shoulder * 0.85, top + h * 0.2)
        ..quadraticBezierTo(cx + hip * 0.9, top + h * 0.36, cx + hip, top + h * 0.5)
        ..lineTo(cx + hip * 0.7, top + h)
        ..moveTo(cx, top + h * 0.52)
        ..lineTo(cx, top + h * 0.98);
    }
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(PoseGhostPainter old) => old.pose != pose || old.color != color;
}
