import 'package:flutter/material.dart';

import '../app_tokens.dart';
import '../au_theme.dart';
import '../haptics.dart';

enum AuButtonVariant { primary, secondary, ghost, destructive }

enum AuButtonSize { regular, compact }

/// The Aurum button.
///
/// * `primary` — gold gradient with a soft glow; one per screen.
/// * `secondary` — outlined surface button.
/// * `ghost` — text-only, for tertiary actions.
/// * `destructive` — danger tone for irreversible actions.
///
/// Shows a spinner while [loading] (width is preserved), presses with a
/// subtle scale and a light haptic. Always at least 44pt tall.
class AuButton extends StatefulWidget {
  const AuButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.variant = AuButtonVariant.primary,
    this.size = AuButtonSize.regular,
    this.icon,
    this.trailingIcon,
    this.loading = false,
    this.expand = true,
    this.haptic = true,
    this.semanticLabel,
  });

  const AuButton.secondary({
    super.key,
    required this.label,
    required this.onPressed,
    this.size = AuButtonSize.regular,
    this.icon,
    this.trailingIcon,
    this.loading = false,
    this.expand = true,
    this.haptic = true,
    this.semanticLabel,
  }) : variant = AuButtonVariant.secondary;

  const AuButton.ghost({
    super.key,
    required this.label,
    required this.onPressed,
    this.size = AuButtonSize.regular,
    this.icon,
    this.trailingIcon,
    this.loading = false,
    this.expand = false,
    this.haptic = true,
    this.semanticLabel,
  }) : variant = AuButtonVariant.ghost;

  const AuButton.destructive({
    super.key,
    required this.label,
    required this.onPressed,
    this.size = AuButtonSize.regular,
    this.icon,
    this.trailingIcon,
    this.loading = false,
    this.expand = true,
    this.haptic = true,
    this.semanticLabel,
  }) : variant = AuButtonVariant.destructive;

  final String label;
  final VoidCallback? onPressed;
  final AuButtonVariant variant;
  final AuButtonSize size;
  final IconData? icon;
  final IconData? trailingIcon;
  final bool loading;
  final bool expand;
  final bool haptic;
  final String? semanticLabel;

  @override
  State<AuButton> createState() => _AuButtonState();
}

class _AuButtonState extends State<AuButton> {
  bool _pressed = false;

  bool get _enabled => widget.onPressed != null && !widget.loading;

  void _setPressed(bool value) {
    if (_pressed != value && mounted) setState(() => _pressed = value);
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final height = widget.size == AuButtonSize.compact ? kAuTouchTarget : 54.0;
    final (Color fg, Color? bg, Gradient? gradient, BoxBorder? border, List<BoxShadow>? shadow) = switch (widget.variant) {
      AuButtonVariant.primary => (au.onAccent, null, au.goldGradient, null, _enabled ? au.glow : null),
      AuButtonVariant.secondary => (au.textPrimary, au.surfaceRaised, null, Border.all(color: au.borderStrong), null),
      AuButtonVariant.ghost => (au.accent, Colors.transparent, null, null, null),
      AuButtonVariant.destructive => (Colors.white, au.danger, null, null, null),
    };
    final textStyle = au.text.label.copyWith(
      color: fg,
      fontSize: widget.size == AuButtonSize.compact ? 14 : 15,
      fontWeight: FontWeight.w700,
      letterSpacing: 0.2,
    );

    final content = Row(
      mainAxisSize: widget.expand ? MainAxisSize.max : MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (widget.icon != null) ...[Icon(widget.icon, size: 20, color: fg), const SizedBox(width: AuSpacing.s2)],
        Flexible(child: Text(widget.label, style: textStyle, maxLines: 1, overflow: TextOverflow.ellipsis)),
        if (widget.trailingIcon != null) ...[const SizedBox(width: AuSpacing.s2), Icon(widget.trailingIcon, size: 20, color: fg)],
      ],
    );

    return Semantics(
      button: true,
      enabled: _enabled,
      label: widget.semanticLabel ?? widget.label,
      excludeSemantics: true,
      child: GestureDetector(
        onTapDown: _enabled ? (_) => _setPressed(true) : null,
        onTapUp: _enabled ? (_) => _setPressed(false) : null,
        onTapCancel: () => _setPressed(false),
        onTap: _enabled
            ? () {
                if (widget.haptic) AuHaptics.light();
                widget.onPressed!();
              }
            : null,
        child: AnimatedScale(
          scale: _pressed ? 0.97 : 1,
          duration: AuMotion.fast,
          curve: AuMotion.easeOut,
          child: AnimatedOpacity(
            duration: AuMotion.fast,
            opacity: widget.onPressed == null ? 0.45 : 1,
            child: Container(
              height: height,
              constraints: const BoxConstraints(minWidth: kAuTouchTarget),
              padding: EdgeInsetsDirectional.symmetric(
                horizontal: widget.variant == AuButtonVariant.ghost ? AuSpacing.s3 : AuSpacing.s5,
              ),
              decoration: BoxDecoration(
                color: bg,
                gradient: gradient,
                border: border,
                boxShadow: shadow,
                borderRadius: BorderRadius.circular(AuRadius.md),
              ),
              child: Center(
                widthFactor: widget.expand ? null : 1,
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    Opacity(opacity: widget.loading ? 0 : 1, child: content),
                    if (widget.loading)
                      SizedBox.square(
                        dimension: 20,
                        child: CircularProgressIndicator(strokeWidth: 2.2, color: fg),
                      ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Circular 44pt icon button with optional tonal background.
class AuIconButton extends StatelessWidget {
  const AuIconButton({
    super.key,
    required this.icon,
    required this.onPressed,
    required this.tooltip,
    this.filled = false,
    this.color,
    this.size = kAuTouchTarget,
  });

  final IconData icon;
  final VoidCallback? onPressed;
  final String tooltip;
  final bool filled;
  final Color? color;
  final double size;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Tooltip(
      message: tooltip,
      child: Material(
        color: filled ? au.surfaceRaised : Colors.transparent,
        shape: CircleBorder(side: filled ? BorderSide(color: au.border) : BorderSide.none),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onPressed == null
              ? null
              : () {
                  AuHaptics.selection();
                  onPressed!();
                },
          child: SizedBox.square(
            dimension: size,
            child: Icon(icon, size: size * 0.5, color: color ?? au.textPrimary),
          ),
        ),
      ),
    );
  }
}
