import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../l10n/gen/au_localizations.dart';
import '../../models/workouts.dart';
import '../app_tokens.dart';
import '../au_theme.dart';
import '../haptics.dart';

/// One row of the workout logger:
///
/// `[set badge] [previous] [weight] [reps|time] [✓]`
///
/// * Values arrive prefilled (last performance → template targets) so most
///   sets are a single tap on ✓.
/// * Tapping the "previous" cell copies last time's values (same as last).
/// * Inputs are numeric, centred and always LTR (digits), the row mirrors in RTL.
/// * Completing a set fires a medium haptic and tints the row.
class AuSetRow extends StatefulWidget {
  const AuSetRow({
    super.key,
    required this.setNumber,
    required this.onToggleComplete,
    this.setType = SetType.working,
    this.previous,
    this.onSameAsLast,
    this.weight,
    this.reps,
    this.durationSeconds,
    this.showWeight = true,
    this.showReps = true,
    this.showDuration = false,
    this.weightUnit = 'kg',
    this.completed = false,
    this.active = false,
    this.onWeightChanged,
    this.onRepsChanged,
    this.onDurationChanged,
    this.rpe,
    this.onRpeTap,
    this.onLongPress,
    this.repsHint,
  });

  final int setNumber;
  final SetType setType;
  final String? previous;
  final VoidCallback? onSameAsLast;

  /// Weight in the display unit.
  final double? weight;
  final int? reps;
  final int? durationSeconds;
  final bool showWeight;
  final bool showReps;
  final bool showDuration;
  final String weightUnit;
  final bool completed;
  final bool active;
  final ValueChanged<double?>? onWeightChanged;
  final ValueChanged<int?>? onRepsChanged;
  final ValueChanged<int?>? onDurationChanged;
  final VoidCallback onToggleComplete;
  final double? rpe;
  final VoidCallback? onRpeTap;
  final VoidCallback? onLongPress;

  /// Target range shown as hint, e.g. `8–10`.
  final String? repsHint;

  @override
  State<AuSetRow> createState() => _AuSetRowState();
}

class _AuSetRowState extends State<AuSetRow> {
  late final TextEditingController _weight = TextEditingController(text: _fmtWeight(widget.weight));
  late final TextEditingController _reps = TextEditingController(text: widget.reps?.toString() ?? '');
  late final TextEditingController _duration = TextEditingController(text: widget.durationSeconds?.toString() ?? '');
  final _weightFocus = FocusNode();
  final _repsFocus = FocusNode();
  final _durationFocus = FocusNode();

  static String _fmtWeight(double? v) {
    if (v == null) return '';
    final rounded = double.parse(v.toStringAsFixed(2));
    return rounded == rounded.roundToDouble() ? rounded.toStringAsFixed(0) : rounded.toString();
  }

  @override
  void didUpdateWidget(AuSetRow old) {
    super.didUpdateWidget(old);
    // Sync external changes (prefill, apply-to-remaining) unless the user is typing.
    if (!_weightFocus.hasFocus && old.weight != widget.weight) _weight.text = _fmtWeight(widget.weight);
    if (!_repsFocus.hasFocus && old.reps != widget.reps) _reps.text = widget.reps?.toString() ?? '';
    if (!_durationFocus.hasFocus && old.durationSeconds != widget.durationSeconds) {
      _duration.text = widget.durationSeconds?.toString() ?? '';
    }
  }

  @override
  void dispose() {
    _weight.dispose();
    _reps.dispose();
    _duration.dispose();
    _weightFocus.dispose();
    _repsFocus.dispose();
    _durationFocus.dispose();
    super.dispose();
  }

  String _typeGlyph(AuL10n l10n) => switch (widget.setType) {
        SetType.warmup => 'W',
        SetType.drop => 'D',
        SetType.failure => 'F',
        SetType.amrap => 'A',
        SetType.working => '${widget.setNumber}',
      };

  String _typeLabel(AuL10n l10n) => switch (widget.setType) {
        SetType.warmup => l10n.setTypeWarmup,
        SetType.working => l10n.setTypeWorking,
        SetType.drop => l10n.setTypeDrop,
        SetType.failure => l10n.setTypeFailure,
        SetType.amrap => l10n.setTypeAmrap,
      };

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = AuL10n.of(context);
    final done = widget.completed;
    final badgeColor = switch (widget.setType) {
      SetType.warmup => au.warning,
      SetType.drop => au.info,
      SetType.failure => au.danger,
      SetType.amrap => au.protein,
      SetType.working => au.textSecondary,
    };

    return AnimatedContainer(
      duration: AuMotion.base,
      curve: AuMotion.easeOut,
      constraints: const BoxConstraints(minHeight: 56),
      padding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s2, vertical: AuSpacing.s1),
      decoration: BoxDecoration(
        color: done
            ? au.success.withValues(alpha: au.isDark ? 0.10 : 0.08)
            : widget.active
                ? au.accent.withValues(alpha: 0.06)
                : Colors.transparent,
        borderRadius: BorderRadius.circular(AuRadius.sm),
        border: widget.active && !done ? Border.all(color: au.accent.withValues(alpha: 0.35)) : null,
      ),
      child: Row(
        children: [
          // Set badge (long-press for set type / delete).
          Semantics(
            label: '${l10n.setNumber(widget.setNumber)}, ${_typeLabel(l10n)}',
            button: widget.onLongPress != null,
            excludeSemantics: true,
            child: GestureDetector(
              onLongPress: widget.onLongPress == null
                  ? null
                  : () {
                      AuHaptics.medium();
                      widget.onLongPress!();
                    },
              child: SizedBox(
                width: 36,
                height: kAuTouchTarget,
                child: Center(
                  child: Container(
                    width: 28,
                    height: 28,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: badgeColor.withValues(alpha: 0.14),
                      borderRadius: BorderRadius.circular(AuRadius.xs),
                    ),
                    child: Text(_typeGlyph(l10n), style: au.text.label.copyWith(color: badgeColor)),
                  ),
                ),
              ),
            ),
          ),
          // Previous performance: tap = same as last.
          Expanded(
            flex: 5,
            child: Semantics(
              button: widget.onSameAsLast != null,
              label: widget.previous == null ? null : '${l10n.previous}: ${widget.previous}. ${l10n.sameAsLast}',
              excludeSemantics: true,
              child: InkWell(
                borderRadius: BorderRadius.circular(AuRadius.xs),
                onTap: widget.previous == null || widget.onSameAsLast == null
                    ? null
                    : () {
                        AuHaptics.selection();
                        widget.onSameAsLast!();
                      },
                child: SizedBox(
                  height: kAuTouchTarget,
                  child: Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: Padding(
                      padding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s1),
                      child: Directionality(
                        textDirection: TextDirection.ltr,
                        child: Text(
                          widget.previous ?? '—',
                          style: au.text.bodySmall.copyWith(color: au.textMuted, fontFeatures: const [FontFeature.tabularFigures()]),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
          if (widget.showWeight)
            Expanded(
              flex: 4,
              child: _NumberCell(
                controller: _weight,
                focusNode: _weightFocus,
                decimal: true,
                suffix: widget.weightUnit,
                semanticLabel: '${l10n.weight} ${widget.weightUnit}',
                done: done,
                onChanged: (v) => widget.onWeightChanged?.call(double.tryParse(v.replaceAll(',', '.'))),
              ),
            ),
          if (widget.showReps) ...[
            const SizedBox(width: AuSpacing.s2),
            Expanded(
              flex: 3,
              child: _NumberCell(
                controller: _reps,
                focusNode: _repsFocus,
                hint: widget.repsHint,
                suffix: null,
                semanticLabel: l10n.reps,
                done: done,
                onChanged: (v) => widget.onRepsChanged?.call(int.tryParse(v)),
              ),
            ),
          ],
          if (widget.showDuration) ...[
            const SizedBox(width: AuSpacing.s2),
            Expanded(
              flex: 3,
              child: _NumberCell(
                controller: _duration,
                focusNode: _durationFocus,
                suffix: 's',
                semanticLabel: l10n.duration,
                done: done,
                onChanged: (v) => widget.onDurationChanged?.call(int.tryParse(v)),
              ),
            ),
          ],
          if (widget.onRpeTap != null)
            Semantics(
              button: true,
              label: '${l10n.rpe} ${widget.rpe?.toStringAsFixed(widget.rpe == widget.rpe?.roundToDouble() ? 0 : 1) ?? ''}',
              excludeSemantics: true,
              child: InkWell(
                onTap: widget.onRpeTap,
                borderRadius: BorderRadius.circular(AuRadius.xs),
                child: SizedBox(
                  width: 36,
                  height: kAuTouchTarget,
                  child: Center(
                    child: Text(
                      widget.rpe == null ? l10n.rpe : widget.rpe!.toStringAsFixed(widget.rpe == widget.rpe!.roundToDouble() ? 0 : 1),
                      style: au.text.caption.copyWith(color: widget.rpe == null ? au.textMuted : au.accent),
                    ),
                  ),
                ),
              ),
            ),
          const SizedBox(width: AuSpacing.s1),
          _DoneButton(
            done: done,
            label: done ? l10n.setDone(widget.setNumber) : l10n.markSetDone(widget.setNumber),
            onTap: () {
              FocusScope.of(context).unfocus();
              if (!done) AuHaptics.medium();
              widget.onToggleComplete();
            },
          ),
        ],
      ),
    );
  }
}

class _NumberCell extends StatelessWidget {
  const _NumberCell({
    required this.controller,
    required this.focusNode,
    required this.onChanged,
    required this.semanticLabel,
    required this.done,
    this.decimal = false,
    this.suffix,
    this.hint,
  });

  final TextEditingController controller;
  final FocusNode focusNode;
  final ValueChanged<String> onChanged;
  final String semanticLabel;
  final bool done;
  final bool decimal;
  final String? suffix;
  final String? hint;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Semantics(
      label: semanticLabel,
      textField: true,
      child: SizedBox(
        height: kAuTouchTarget,
        child: Directionality(
          textDirection: TextDirection.ltr,
          child: TextField(
            controller: controller,
            focusNode: focusNode,
            onChanged: onChanged,
            textAlign: TextAlign.center,
            keyboardType: TextInputType.numberWithOptions(decimal: decimal),
            textInputAction: TextInputAction.next,
            inputFormatters: [
              FilteringTextInputFormatter.allow(decimal ? RegExp(r'[0-9.,]') : RegExp(r'[0-9]')),
              LengthLimitingTextInputFormatter(decimal ? 6 : 4),
            ],
            onTap: () => controller.selection = TextSelection(baseOffset: 0, extentOffset: controller.text.length),
            style: au.text.title.copyWith(
              fontFeatures: const [FontFeature.tabularFigures()],
              color: done ? au.textSecondary : au.textPrimary,
            ),
            cursorColor: au.accent,
            decoration: InputDecoration(
              isDense: true,
              hintText: hint,
              hintStyle: au.text.bodySmall.copyWith(color: au.textMuted),
              suffixText: suffix,
              suffixStyle: au.text.caption.copyWith(color: au.textMuted),
              contentPadding: const EdgeInsets.symmetric(horizontal: 6, vertical: 10),
              filled: true,
              fillColor: done ? Colors.transparent : au.surfaceSunken,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(AuRadius.sm), borderSide: BorderSide.none),
              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(AuRadius.sm), borderSide: BorderSide.none),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AuRadius.sm),
                borderSide: BorderSide(color: au.accent, width: 1.5),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _DoneButton extends StatelessWidget {
  const _DoneButton({required this.done, required this.onTap, required this.label});

  final bool done;
  final VoidCallback onTap;
  final String label;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Semantics(
      button: true,
      checked: done,
      label: label,
      excludeSemantics: true,
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: onTap,
        child: SizedBox.square(
          dimension: kAuTouchTarget,
          child: Center(
            child: AnimatedContainer(
              duration: AuMotion.base,
              curve: AuMotion.easeOut,
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                gradient: done ? au.goldGradient : null,
                color: done ? null : au.surfaceSunken,
                borderRadius: BorderRadius.circular(AuRadius.sm),
                border: done ? null : Border.all(color: au.borderStrong),
              ),
              child: AnimatedSwitcher(
                duration: AuMotion.fast,
                transitionBuilder: (child, anim) => ScaleTransition(scale: anim, child: child),
                child: Icon(
                  Icons.check_rounded,
                  key: ValueKey(done),
                  size: 22,
                  color: done ? au.onAccent : au.textMuted,
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
