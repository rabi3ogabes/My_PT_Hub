import 'package:flutter/material.dart';

import '../app_tokens.dart';
import '../au_theme.dart';
import '../haptics.dart';
import 'au_card.dart';

/// A single metric with label, big value, unit and optional trend.
class AuStatTile extends StatelessWidget {
  const AuStatTile({
    super.key,
    required this.label,
    required this.value,
    this.unit,
    this.icon,
    this.trend,
    this.trendPositive = true,
    this.color,
    this.onTap,
  });

  final String label;
  final String value;
  final String? unit;
  final IconData? icon;
  final String? trend;
  final bool trendPositive;
  final Color? color;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final tone = color ?? au.accent;
    return AuCard(
      onTap: onTap,
      semanticLabel: '$label $value ${unit ?? ''}',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              if (icon != null) ...[Icon(icon, size: 16, color: tone), const SizedBox(width: AuSpacing.s2)],
              Expanded(
                child: Text(label.toUpperCase(),
                    style: au.text.overline.copyWith(color: au.textMuted), maxLines: 1, overflow: TextOverflow.ellipsis),
              ),
            ],
          ),
          const SizedBox(height: AuSpacing.s2),
          Row(
            crossAxisAlignment: CrossAxisAlignment.baseline,
            textBaseline: TextBaseline.alphabetic,
            children: [
              Flexible(child: Text(value, style: au.text.numeric.copyWith(fontSize: 26), maxLines: 1, overflow: TextOverflow.ellipsis)),
              if (unit != null) ...[
                const SizedBox(width: AuSpacing.s1),
                Text(unit!, style: au.text.label.copyWith(color: au.textSecondary)),
              ],
            ],
          ),
          if (trend != null) ...[
            const SizedBox(height: AuSpacing.s1),
            Text(trend!, style: au.text.caption.copyWith(color: trendPositive ? au.success : au.danger)),
          ],
        ],
      ),
    );
  }
}

/// A tappable row for lists and settings.
class AuListRow extends StatelessWidget {
  const AuListRow({
    super.key,
    required this.title,
    this.subtitle,
    this.leading,
    this.trailing,
    this.onTap,
    this.showChevron,
    this.destructive = false,
    this.dense = false,
  });

  final String title;
  final String? subtitle;
  final Widget? leading;
  final Widget? trailing;
  final VoidCallback? onTap;
  final bool? showChevron;
  final bool destructive;
  final bool dense;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final chevron = showChevron ?? (onTap != null && trailing == null);
    return Material(
      type: MaterialType.transparency,
      child: InkWell(
        onTap: onTap == null
            ? null
            : () {
                AuHaptics.selection();
                onTap!();
              },
        child: ConstrainedBox(
          constraints: BoxConstraints(minHeight: dense ? kAuTouchTarget : 60),
          child: Padding(
            padding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s4, vertical: AuSpacing.s3),
            child: Row(
              children: [
                if (leading != null) ...[leading!, const SizedBox(width: AuSpacing.s3)],
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(title, style: au.text.body.copyWith(fontWeight: FontWeight.w600, color: destructive ? au.danger : au.textPrimary)),
                      if (subtitle != null) ...[
                        const SizedBox(height: 2),
                        Text(subtitle!, style: au.text.bodySmall.copyWith(color: au.textSecondary)),
                      ],
                    ],
                  ),
                ),
                if (trailing != null) ...[const SizedBox(width: AuSpacing.s3), trailing!],
                if (chevron) ...[
                  const SizedBox(width: AuSpacing.s2),
                  AuDirectionalIcon(Icons.chevron_right_rounded, color: au.textMuted),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// Rounded leading icon used in list rows.
class AuIconTile extends StatelessWidget {
  const AuIconTile({super.key, required this.icon, this.color, this.size = 40});

  final IconData icon;
  final Color? color;
  final double size;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final tone = color ?? au.accent;
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(color: tone.withValues(alpha: 0.14), borderRadius: BorderRadius.circular(AuRadius.sm)),
      child: Icon(icon, color: tone, size: size * 0.5),
    );
  }
}

/// Filter / choice chip. Visual height 36, hit area 44.
class AuChip extends StatelessWidget {
  const AuChip({super.key, required this.label, this.selected = false, this.onSelected, this.icon});

  final String label;
  final bool selected;
  final ValueChanged<bool>? onSelected;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Semantics(
      selected: selected,
      button: true,
      label: label,
      excludeSemantics: true,
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: onSelected == null
            ? null
            : () {
                AuHaptics.selection();
                onSelected!(!selected);
              },
        child: Padding(
          padding: const EdgeInsetsDirectional.symmetric(vertical: 4),
          child: AnimatedContainer(
            duration: AuMotion.base,
            curve: AuMotion.easeOut,
            height: 36,
            padding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s4),
            decoration: BoxDecoration(
              color: selected ? au.accent.withValues(alpha: 0.16) : au.surface,
              borderRadius: BorderRadius.circular(AuRadius.pill),
              border: Border.all(color: selected ? au.accent : au.border),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (icon != null) ...[
                  Icon(icon, size: 16, color: selected ? au.accent : au.textSecondary),
                  const SizedBox(width: AuSpacing.s1),
                ],
                Text(label, style: au.text.label.copyWith(color: selected ? au.accent : au.textSecondary)),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

enum AuBadgeTone { accent, danger, success, warning, neutral }

/// Count or short label pill.
class AuBadge extends StatelessWidget {
  const AuBadge({super.key, required this.label, this.tone = AuBadgeTone.accent, this.solid = true});

  /// Formats a count, capping at 99+.
  factory AuBadge.count(int count, {Key? key, AuBadgeTone tone = AuBadgeTone.danger}) =>
      AuBadge(key: key, label: count > 99 ? '99+' : '$count', tone: tone);

  final String label;
  final AuBadgeTone tone;
  final bool solid;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final color = switch (tone) {
      AuBadgeTone.accent => au.accent,
      AuBadgeTone.danger => au.danger,
      AuBadgeTone.success => au.success,
      AuBadgeTone.warning => au.warning,
      AuBadgeTone.neutral => au.textMuted,
    };
    final fg = solid ? (tone == AuBadgeTone.accent ? au.onAccent : Colors.white) : color;
    return Container(
      constraints: const BoxConstraints(minWidth: 20, minHeight: 20),
      padding: const EdgeInsetsDirectional.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: solid ? color : color.withValues(alpha: 0.14),
        borderRadius: BorderRadius.circular(AuRadius.pill),
      ),
      child: Text(
        label,
        textAlign: TextAlign.center,
        style: au.text.caption.copyWith(color: fg, fontWeight: FontWeight.w700, height: 1.2),
      ),
    );
  }
}

/// Circular avatar with initials fallback and optional gold ring.
class AuAvatar extends StatelessWidget {
  const AuAvatar({super.key, required this.name, this.imageUrl, this.size = 44, this.ring = false});

  final String name;
  final String? imageUrl;
  final double size;
  final bool ring;

  String get initials {
    final parts = name.trim().split(RegExp(r'\s+')).where((p) => p.isNotEmpty).toList();
    if (parts.isEmpty) return '·';
    final first = parts.first.characters.first;
    final last = parts.length > 1 ? parts.last.characters.first : '';
    return (first + last).toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final fallback = Container(
      alignment: Alignment.center,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: LinearGradient(
          colors: [au.surfaceRaised, au.surfaceSunken],
          begin: AlignmentDirectional.topStart,
          end: AlignmentDirectional.bottomEnd,
        ),
      ),
      child: Text(initials, style: au.text.label.copyWith(fontSize: size * 0.36, color: au.accent)),
    );
    final image = imageUrl == null || imageUrl!.isEmpty
        ? fallback
        : ClipOval(
            child: Image.network(
              imageUrl!,
              width: size,
              height: size,
              fit: BoxFit.cover,
              errorBuilder: (_, _, _) => fallback,
            ),
          );
    return Semantics(
      label: name,
      image: true,
      child: Container(
        width: size,
        height: size,
        padding: ring ? const EdgeInsets.all(2) : EdgeInsets.zero,
        decoration: ring ? BoxDecoration(shape: BoxShape.circle, gradient: au.goldGradient) : null,
        child: ring
            ? Container(
                padding: const EdgeInsets.all(2),
                decoration: BoxDecoration(shape: BoxShape.circle, color: au.background),
                child: image,
              )
            : image,
      ),
    );
  }
}

/// Streak flame with count, gold gradient when active.
class AuStreakFlame extends StatelessWidget {
  const AuStreakFlame({super.key, required this.count, this.size = 22, this.semanticLabel});

  final int count;
  final double size;
  final String? semanticLabel;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final active = count > 0;
    return Semantics(
      label: semanticLabel,
      excludeSemantics: semanticLabel != null,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          ShaderMask(
            shaderCallback: (rect) => (active
                    ? const LinearGradient(
                        colors: [Color(0xFFF2B544), Color(0xFFD4AF6A), Color(0xFFE76F6F)],
                        begin: Alignment.bottomCenter,
                        end: Alignment.topCenter,
                      )
                    : LinearGradient(colors: [au.textMuted, au.textMuted]))
                .createShader(rect),
            child: Icon(Icons.local_fire_department_rounded, size: size, color: Colors.white),
          ),
          const SizedBox(width: 2),
          Text('$count', style: au.text.label.copyWith(color: active ? au.textPrimary : au.textMuted, fontSize: size * 0.68)),
        ],
      ),
    );
  }
}
