import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../l10n/gen/au_localizations.dart';
import '../app_tokens.dart';
import '../au_theme.dart';

/// Labelled text field. The label sits above the input (calmer than floating
/// labels and reads well in Arabic); errors appear below.
class AuTextField extends StatefulWidget {
  const AuTextField({
    super.key,
    this.label,
    this.hint,
    this.controller,
    this.initialValue,
    this.errorText,
    this.helperText,
    this.obscure = false,
    this.keyboardType,
    this.textInputAction,
    this.onSubmitted,
    this.onChanged,
    this.validator,
    this.autofillHints,
    this.prefixIcon,
    this.suffix,
    this.maxLines = 1,
    this.minLines,
    this.enabled = true,
    this.autofocus = false,
    this.focusNode,
    this.inputFormatters,
    this.textAlign = TextAlign.start,
    this.textCapitalization = TextCapitalization.none,
    this.textDirection,
  });

  final String? label;
  final String? hint;
  final TextEditingController? controller;
  final String? initialValue;
  final String? errorText;
  final String? helperText;
  final bool obscure;
  final TextInputType? keyboardType;
  final TextInputAction? textInputAction;
  final ValueChanged<String>? onSubmitted;
  final ValueChanged<String>? onChanged;
  final FormFieldValidator<String>? validator;
  final Iterable<String>? autofillHints;
  final IconData? prefixIcon;
  final Widget? suffix;
  final int maxLines;
  final int? minLines;
  final bool enabled;
  final bool autofocus;
  final FocusNode? focusNode;
  final List<TextInputFormatter>? inputFormatters;
  final TextAlign textAlign;
  final TextCapitalization textCapitalization;

  /// Force a direction (e.g. LTR for email/codes inside an RTL layout).
  final TextDirection? textDirection;

  @override
  State<AuTextField> createState() => _AuTextFieldState();
}

class _AuTextFieldState extends State<AuTextField> {
  late bool _obscured = widget.obscure;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = AuL10n.of(context);
    Widget? suffix = widget.suffix;
    if (widget.obscure) {
      suffix = IconButton(
        tooltip: _obscured ? l10n.showPassword : l10n.hidePassword,
        icon: Icon(_obscured ? Icons.visibility_outlined : Icons.visibility_off_outlined, color: au.textMuted),
        onPressed: () => setState(() => _obscured = !_obscured),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      mainAxisSize: MainAxisSize.min,
      children: [
        if (widget.label != null) ...[
          Text(widget.label!, style: au.text.label.copyWith(color: au.textSecondary)),
          const SizedBox(height: AuSpacing.s2),
        ],
        TextFormField(
          controller: widget.controller,
          initialValue: widget.controller == null ? widget.initialValue : null,
          focusNode: widget.focusNode,
          enabled: widget.enabled,
          autofocus: widget.autofocus,
          obscureText: _obscured,
          keyboardType: widget.keyboardType,
          textInputAction: widget.textInputAction,
          onFieldSubmitted: widget.onSubmitted,
          onChanged: widget.onChanged,
          validator: widget.validator,
          autofillHints: widget.autofillHints,
          maxLines: widget.obscure ? 1 : widget.maxLines,
          minLines: widget.minLines,
          inputFormatters: widget.inputFormatters,
          textAlign: widget.textAlign,
          textCapitalization: widget.textCapitalization,
          textDirection: widget.textDirection,
          style: au.text.body.copyWith(color: au.textPrimary),
          cursorColor: au.accent,
          decoration: InputDecoration(
            hintText: widget.hint,
            errorText: widget.errorText,
            helperText: widget.helperText,
            helperStyle: au.text.caption.copyWith(color: au.textMuted),
            errorMaxLines: 3,
            prefixIcon: widget.prefixIcon == null ? null : Icon(widget.prefixIcon, color: au.textMuted, size: 20),
            suffixIcon: suffix,
          ),
        ),
      ],
    );
  }
}
