import 'package:flutter/material.dart';

import '../app_tokens.dart';
import '../au_theme.dart';

enum AuCardVariant {
  /// Default surface with a hairline border.
  surface,

  /// Raised surface with soft elevation (sheets, popovers).
  raised,

  /// Hero card: deep surface with a gold hairline and glow.
  hero,

  /// Sunken well (inputs groups, secondary info).
  sunken,
}

/// Rounded container used for every grouped surface.
class AuCard extends StatelessWidget {
  const AuCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsetsDirectional.all(AuSpacing.s4),
    this.onTap,
    this.variant = AuCardVariant.surface,
    this.radius = AuRadius.lg,
    this.semanticLabel,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final VoidCallback? onTap;
  final AuCardVariant variant;
  final double radius;
  final String? semanticLabel;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final borderRadius = BorderRadius.circular(radius);
    final decoration = switch (variant) {
      AuCardVariant.surface => BoxDecoration(
          color: au.surface,
          borderRadius: borderRadius,
          border: Border.all(color: au.border),
        ),
      AuCardVariant.raised => BoxDecoration(
          color: au.surfaceRaised,
          borderRadius: borderRadius,
          border: Border.all(color: au.border),
          boxShadow: au.elevation2,
        ),
      AuCardVariant.hero => BoxDecoration(
          borderRadius: borderRadius,
          gradient: LinearGradient(
            begin: AlignmentDirectional.topStart,
            end: AlignmentDirectional.bottomEnd,
            colors: au.isDark
                ? [au.surfaceRaised, au.surface, au.background]
                : [au.surface, au.surface, au.surfaceSunken],
          ),
          border: Border.all(color: au.accent.withValues(alpha: 0.45)),
          boxShadow: [BoxShadow(color: au.accent.withValues(alpha: au.isDark ? 0.12 : 0.10), blurRadius: 28, offset: const Offset(0, 10))],
        ),
      AuCardVariant.sunken => BoxDecoration(color: au.surfaceSunken, borderRadius: borderRadius),
    };

    Widget content = Padding(padding: padding, child: child);
    if (onTap != null) {
      content = Material(
        type: MaterialType.transparency,
        child: InkWell(borderRadius: borderRadius, onTap: onTap, child: content),
      );
    }
    return Semantics(
      container: true,
      button: onTap != null,
      label: semanticLabel,
      child: DecoratedBox(decoration: decoration, child: ClipRRect(borderRadius: borderRadius, child: content)),
    );
  }
}

/// Section title with an optional trailing action ("See all").
class AuSectionHeader extends StatelessWidget {
  const AuSectionHeader({super.key, required this.title, this.actionLabel, this.onAction, this.overline});

  final String title;
  final String? overline;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Padding(
      padding: const EdgeInsetsDirectional.only(top: AuSpacing.s2, bottom: AuSpacing.s3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (overline != null)
                  Text(overline!.toUpperCase(), style: au.text.overline.copyWith(color: au.accent)),
                Semantics(header: true, child: Text(title, style: au.text.h3)),
              ],
            ),
          ),
          if (actionLabel != null && onAction != null)
            TextButton(onPressed: onAction, child: Text(actionLabel!)),
        ],
      ),
    );
  }
}

/// An icon that mirrors in RTL (chevrons, arrows).
class AuDirectionalIcon extends StatelessWidget {
  const AuDirectionalIcon(this.icon, {super.key, this.size, this.color});

  final IconData icon;
  final double? size;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final rtl = Directionality.of(context) == TextDirection.rtl;
    final child = Icon(icon, size: size, color: color);
    return rtl ? Transform.flip(flipX: true, child: child) : child;
  }
}
