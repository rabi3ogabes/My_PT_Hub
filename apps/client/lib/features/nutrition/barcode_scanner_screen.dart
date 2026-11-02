import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../shared/l10n_ext.dart';

/// Full-screen barcode scanner; pops with the scanned EAN/UPC code.
class BarcodeScannerScreen extends StatefulWidget {
  const BarcodeScannerScreen({super.key});

  @override
  State<BarcodeScannerScreen> createState() => _BarcodeScannerScreenState();
}

class _BarcodeScannerScreenState extends State<BarcodeScannerScreen> {
  final _controller = MobileScannerController(
    formats: const [BarcodeFormat.ean13, BarcodeFormat.ean8, BarcodeFormat.upcA, BarcodeFormat.upcE],
    detectionSpeed: DetectionSpeed.noDuplicates,
  );
  bool _done = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) {
    if (_done) return;
    final code = capture.barcodes.map((b) => b.rawValue).whereType<String>().firstOrNull;
    if (code == null) return;
    _done = true;
    AuHaptics.medium();
    Navigator.of(context).pop(code);
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        foregroundColor: Colors.white,
        title: Text(context.l10n.scanBarcode),
        actions: [
          IconButton(tooltip: context.l10n.torch, icon: const Icon(Icons.flash_on_rounded), onPressed: _controller.toggleTorch),
        ],
      ),
      extendBodyBehindAppBar: true,
      body: Stack(
        fit: StackFit.expand,
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: _onDetect,
            errorBuilder: (context, error) => Center(
              child: Padding(
                padding: const EdgeInsetsDirectional.all(AuSpacing.s8),
                child: Text(context.l10n.cameraUnavailable, style: au.text.body.copyWith(color: Colors.white), textAlign: TextAlign.center),
              ),
            ),
          ),
          Center(
            child: Container(
              width: 280,
              height: 170,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(AuRadius.lg),
                border: Border.all(color: au.accent, width: 2.5),
              ),
            ),
          ),
          PositionedDirectional(
            start: 0,
            end: 0,
            bottom: 80,
            child: Text(
              context.l10n.scanHint,
              textAlign: TextAlign.center,
              style: au.text.body.copyWith(color: Colors.white),
            ),
          ),
        ],
      ),
    );
  }
}
