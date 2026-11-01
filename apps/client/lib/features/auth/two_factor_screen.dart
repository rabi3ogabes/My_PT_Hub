import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'auth_layout.dart';

class TwoFactorScreen extends ConsumerStatefulWidget {
  const TwoFactorScreen({super.key, required this.challenge});

  final String challenge;

  @override
  ConsumerState<TwoFactorScreen> createState() => _TwoFactorScreenState();
}

class _TwoFactorScreenState extends ConsumerState<TwoFactorScreen> {
  final _code = TextEditingController();
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _code.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_code.text.trim().length < 6) return;
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      await ref.read(authControllerProvider.notifier).completeTwoFactor(widget.challenge, _code.text.trim());
    } on ApiFailure catch (f) {
      if (!mounted) return;
      AuHaptics.error();
      setState(() => _error = f.fieldError('code') ?? auFailureMessage(context, f));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return AuthLayout(
      title: context.l10n.twoFactorTitle,
      subtitle: context.l10n.twoFactorSubtitle,
      children: [
        AuTextField(
          controller: _code,
          autofocus: true,
          keyboardType: TextInputType.number,
          textAlign: TextAlign.center,
          textDirection: TextDirection.ltr,
          autofillHints: const [AutofillHints.oneTimeCode],
          inputFormatters: [FilteringTextInputFormatter.digitsOnly, LengthLimitingTextInputFormatter(6)],
          errorText: _error,
          onChanged: (v) {
            if (v.length == 6) _submit();
          },
        ),
        const SizedBox(height: AuSpacing.s2),
        Text(context.l10n.twoFactorHint, style: au.text.caption.copyWith(color: au.textMuted), textAlign: TextAlign.center),
        const SizedBox(height: AuSpacing.s6),
        AuButton(label: context.core.confirm, loading: _loading, onPressed: _submit),
      ],
    );
  }
}
