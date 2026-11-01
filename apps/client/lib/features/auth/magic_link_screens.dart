import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'auth_layout.dart';

/// Request a passwordless sign-in link.
class MagicLinkRequestScreen extends ConsumerStatefulWidget {
  const MagicLinkRequestScreen({super.key});

  @override
  ConsumerState<MagicLinkRequestScreen> createState() => _MagicLinkRequestScreenState();
}

class _MagicLinkRequestScreenState extends ConsumerState<MagicLinkRequestScreen> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  bool _loading = false;
  bool _sent = false;

  @override
  void dispose() {
    _email.dispose();
    super.dispose();
  }

  Future<void> _send() async {
    if (!(_form.currentState?.validate() ?? false)) return;
    setState(() => _loading = true);
    try {
      await ref.read(authControllerProvider.notifier).requestMagicLink(_email.text.trim());
      if (mounted) setState(() => _sent = true);
    } on ApiFailure catch (f) {
      if (mounted) showError(context, f);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final au = context.au;
    if (_sent) {
      return AuthLayout(
        title: l10n.checkInbox,
        subtitle: l10n.magicLinkSent(_email.text.trim()),
        children: [
          AuCard(
            variant: AuCardVariant.sunken,
            child: Row(
              children: [
                Icon(Icons.mark_email_read_outlined, color: au.accent),
                const SizedBox(width: AuSpacing.s3),
                Expanded(child: Text(l10n.magicLinkHint, style: au.text.bodySmall.copyWith(color: au.textSecondary))),
              ],
            ),
          ),
          const SizedBox(height: AuSpacing.s6),
          AuButton.secondary(label: l10n.resend, onPressed: _loading ? null : _send),
          const SizedBox(height: AuSpacing.s3),
          AuButton.ghost(label: l10n.backToSignIn, expand: true, onPressed: () => context.go(Routes.signIn)),
        ],
      );
    }
    return AuthLayout(
      title: l10n.magicLinkTitle,
      subtitle: l10n.magicLinkSubtitle,
      children: [
        Form(
          key: _form,
          child: AuTextField(
            controller: _email,
            label: context.core.email,
            keyboardType: TextInputType.emailAddress,
            autofillHints: const [AutofillHints.email],
            textDirection: TextDirection.ltr,
            prefixIcon: Icons.mail_outline_rounded,
            validator: (v) => Validators.email(context, v),
            onSubmitted: (_) => _send(),
          ),
        ),
        const SizedBox(height: AuSpacing.s6),
        AuButton(label: l10n.sendLink, loading: _loading, onPressed: _send),
      ],
    );
  }
}

/// Handles `https://{domain}/auth/magic/{token}` deep links.
class MagicLinkVerifyScreen extends ConsumerStatefulWidget {
  const MagicLinkVerifyScreen({super.key, required this.token});

  final String token;

  @override
  ConsumerState<MagicLinkVerifyScreen> createState() => _MagicLinkVerifyScreenState();
}

class _MagicLinkVerifyScreenState extends ConsumerState<MagicLinkVerifyScreen> {
  Object? _error;

  @override
  void initState() {
    super.initState();
    _verify();
  }

  Future<void> _verify() async {
    setState(() => _error = null);
    try {
      await ref.read(authControllerProvider.notifier).verifyMagicLink(widget.token);
    } on ApiFailure catch (f) {
      if (mounted) setState(() => _error = f);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return AuthLayout(
      title: _error == null ? l10n.signingYouIn : l10n.linkExpiredTitle,
      subtitle: _error == null ? null : l10n.linkExpiredMessage,
      children: [
        if (_error == null)
          const Center(child: CircularProgressIndicator())
        else ...[
          AuButton(label: l10n.sendNewLink, onPressed: () => context.go(Routes.magicLink)),
          const SizedBox(height: AuSpacing.s3),
          AuButton.secondary(label: context.core.retry, onPressed: _verify),
        ],
      ],
    );
  }
}
