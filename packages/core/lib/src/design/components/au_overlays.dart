import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/semantics.dart';

import '../app_tokens.dart';
import '../au_theme.dart';
import '../haptics.dart';

/// Presents a rounded modal sheet with a grab handle, optional title, safe
/// area and keyboard avoidance.
Future<T?> showAuBottomSheet<T>({
  required BuildContext context,
  required WidgetBuilder builder,
  String? title,
  bool expand = false,
  bool isDismissible = true,
}) {
  return showModalBottomSheet<T>(
    context: context,
    isScrollControlled: true,
    isDismissible: isDismissible,
    useSafeArea: true,
    builder: (context) => AuBottomSheet(title: title, expand: expand, child: Builder(builder: builder)),
  );
}

class AuBottomSheet extends StatelessWidget {
  const AuBottomSheet({super.key, required this.child, this.title, this.expand = false});

  final Widget child;
  final String? title;
  final bool expand;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final media = MediaQuery.of(context);
    final content = Column(
      mainAxisSize: expand ? MainAxisSize.max : MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const SizedBox(height: AuSpacing.s3),
        Center(
          child: Container(
            width: 40,
            height: 4,
            decoration: BoxDecoration(color: au.borderStrong, borderRadius: BorderRadius.circular(AuRadius.pill)),
          ),
        ),
        if (title != null)
          Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s4, AuSpacing.s5, AuSpacing.s2),
            child: Semantics(header: true, child: Text(title!, style: au.text.h3)),
          ),
        if (expand) Expanded(child: child) else Flexible(child: child),
      ],
    );
    return Padding(
      padding: EdgeInsets.only(bottom: media.viewInsets.bottom),
      child: ConstrainedBox(
        constraints: BoxConstraints(maxHeight: media.size.height * 0.92),
        child: content,
      ),
    );
  }
}

enum AuToastTone { neutral, success, warning, danger }

/// Elegant top toast (auto-dismiss, swipe away, screen-reader announced).
abstract final class AuToast {
  static OverlayEntry? _current;
  static Timer? _timer;

  static void show(
    BuildContext context,
    String message, {
    AuToastTone tone = AuToastTone.neutral,
    IconData? icon,
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration = const Duration(seconds: 3),
  }) {
    final overlay = Overlay.maybeOf(context, rootOverlay: true);
    if (overlay == null) return;
    dismiss();
    if (tone == AuToastTone.danger) {
      AuHaptics.error();
    } else if (tone == AuToastTone.success) {
      AuHaptics.light();
    }
    SemanticsService.sendAnnouncement(View.of(context), message, Directionality.of(context));
    final entry = OverlayEntry(
      builder: (_) => _ToastView(
        message: message,
        tone: tone,
        icon: icon,
        actionLabel: actionLabel,
        onAction: onAction == null
            ? null
            : () {
                dismiss();
                onAction();
              },
        onDismiss: dismiss,
      ),
    );
    _current = entry;
    overlay.insert(entry);
    _timer = Timer(duration, dismiss);
  }

  static void dismiss() {
    _timer?.cancel();
    _timer = null;
    _current?.remove();
    _current = null;
  }
}

class _ToastView extends StatelessWidget {
  const _ToastView({
    required this.message,
    required this.tone,
    required this.onDismiss,
    this.icon,
    this.actionLabel,
    this.onAction,
  });

  final String message;
  final AuToastTone tone;
  final IconData? icon;
  final String? actionLabel;
  final VoidCallback? onAction;
  final VoidCallback onDismiss;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final color = switch (tone) {
      AuToastTone.neutral => au.accent,
      AuToastTone.success => au.success,
      AuToastTone.warning => au.warning,
      AuToastTone.danger => au.danger,
    };
    final glyph = icon ??
        switch (tone) {
          AuToastTone.neutral => Icons.info_outline_rounded,
          AuToastTone.success => Icons.check_circle_rounded,
          AuToastTone.warning => Icons.warning_amber_rounded,
          AuToastTone.danger => Icons.error_outline_rounded,
        };
    return SafeArea(
      child: Align(
        alignment: Alignment.topCenter,
        child: TweenAnimationBuilder<double>(
          tween: Tween(begin: 0, end: 1),
          duration: AuMotion.base,
          curve: AuMotion.easeOut,
          builder: (_, t, child) => Opacity(opacity: t, child: Transform.translate(offset: Offset(0, -16 * (1 - t)), child: child)),
          child: Dismissible(
            key: const ValueKey('au-toast'),
            direction: DismissDirection.up,
            onDismissed: (_) => onDismiss(),
            child: Padding(
              padding: const EdgeInsetsDirectional.all(AuSpacing.s4),
              child: Material(
                color: au.surfaceRaised,
                elevation: 0,
                borderRadius: BorderRadius.circular(AuRadius.lg),
                child: Container(
                  constraints: const BoxConstraints(maxWidth: 520, minHeight: 52),
                  padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s4, AuSpacing.s3, AuSpacing.s2, AuSpacing.s3),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(AuRadius.lg),
                    border: Border.all(color: color.withValues(alpha: 0.5)),
                    boxShadow: au.elevation3,
                  ),
                  child: Row(
                    children: [
                      Icon(glyph, color: color, size: 22),
                      const SizedBox(width: AuSpacing.s3),
                      Expanded(child: Text(message, style: au.text.bodySmall.copyWith(color: au.textPrimary))),
                      if (actionLabel != null) TextButton(onPressed: onAction, child: Text(actionLabel!)),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
