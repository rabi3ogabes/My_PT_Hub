import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'auth_layout.dart';

class SignInScreen extends ConsumerStatefulWidget {
  const SignInScreen({super.key, this.from});

  /// Where to continue after signing in (set by the auth guard).
  final String? from;

  @override
  ConsumerState<SignInScreen> createState() => _SignInScreenState();
}

class _SignInScreenState extends ConsumerState<SignInScreen> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _loading = false;
  ApiFailure? _failure;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() => _failure = null);
    if (!(_form.currentState?.validate() ?? false)) {
      AuHaptics.error();
      return;
    }
    setState(() => _loading = true);
    try {
      final result = await ref.read(authControllerProvider.notifier).signIn(_email.text.trim(), _password.text);
      if (!mounted) return;
      if (result is LoginTwoFactorRequired) {
        context.push(Uri(path: Routes.twoFactor, queryParameters: {'challenge': result.challenge}).toString());
      }
    } on ApiFailure catch (failure) {
      if (!mounted) return;
      setState(() => _failure = failure);
      if (failure.fieldErrors.isEmpty) showError(context, failure);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _forgotPassword() async {
    final email = _email.text.trim();
    if (Validators.email(context, email) != null) {
      AuToast.show(context, context.l10n.enterEmailFirst, tone: AuToastTone.warning);
      return;
    }
    try {
      await ref.read(authRepositoryProvider).forgotPassword(email);
      if (mounted) AuToast.show(context, context.l10n.resetLinkSent, tone: AuToastTone.success);
    } on ApiFailure catch (f) {
      if (mounted) showError(context, f);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final au = context.au;
    return AuthLayout(
      title: l10n.signInTitle,
      subtitle: l10n.signInSubtitle,
      children: [
        Form(
          key: _form,
          child: AutofillGroup(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                AuTextField(
                  key: const Key('signin-email'),
                  controller: _email,
                  label: context.core.email,
                  hint: 'name@example.com',
                  keyboardType: TextInputType.emailAddress,
                  textInputAction: TextInputAction.next,
                  autofillHints: const [AutofillHints.email],
                  textDirection: TextDirection.ltr,
                  prefixIcon: Icons.mail_outline_rounded,
                  errorText: _failure?.fieldError('email'),
                  validator: (v) => Validators.email(context, v),
                ),
                const SizedBox(height: AuSpacing.s4),
                AuTextField(
                  key: const Key('signin-password'),
                  controller: _password,
                  label: context.core.password,
                  obscure: true,
                  textInputAction: TextInputAction.done,
                  autofillHints: const [AutofillHints.password],
                  prefixIcon: Icons.lock_outline_rounded,
                  errorText: _failure?.fieldError('password'),
                  validator: (v) => Validators.required(context, v),
                  onSubmitted: (_) => _submit(),
                ),
                Align(
                  alignment: AlignmentDirectional.centerEnd,
                  child: TextButton(onPressed: _forgotPassword, child: Text(l10n.forgotPassword)),
                ),
                const SizedBox(height: AuSpacing.s2),
                AuButton(key: const Key('signin-submit'), label: l10n.signIn, loading: _loading, onPressed: _submit),
                const SizedBox(height: AuSpacing.s3),
                AuButton.secondary(
                  label: l10n.emailMeALink,
                  icon: Icons.auto_awesome_rounded,
                  onPressed: () => context.push(Routes.magicLink),
                ),
                const SizedBox(height: AuSpacing.s8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(l10n.newToAurum, style: au.text.bodySmall.copyWith(color: au.textSecondary)),
                    TextButton(onPressed: () => context.go(Routes.signUp), child: Text(l10n.createAccount)),
                  ],
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
