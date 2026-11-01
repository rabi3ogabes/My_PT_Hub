import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'auth_layout.dart';
import 'invite_screen.dart';

/// Account creation: "I have a coach" (invite code) or "Train on my own".
class SignUpScreen extends ConsumerStatefulWidget {
  const SignUpScreen({super.key, this.inviteCode});

  final String? inviteCode;

  @override
  ConsumerState<SignUpScreen> createState() => _SignUpScreenState();
}

class _SignUpScreenState extends ConsumerState<SignUpScreen> {
  final _form = GlobalKey<FormState>();
  late final _code = TextEditingController(text: widget.inviteCode ?? '');
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  late AccountType _type = widget.inviteCode != null ? AccountType.client : AccountType.client;
  bool _hasCoach = true;
  bool _terms = false;
  bool _loading = false;
  ApiFailure? _failure;
  Timer? _debounce;
  AsyncValue<InvitationPreview?> _preview = const AsyncData(null);

  @override
  void initState() {
    super.initState();
    if (widget.inviteCode != null) _lookup(widget.inviteCode!);
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _code.dispose();
    _name.dispose();
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  void _onCodeChanged(String value) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 600), () => _lookup(value));
  }

  Future<void> _lookup(String code) async {
    final trimmed = code.trim();
    if (trimmed.length < 4) {
      setState(() => _preview = const AsyncData(null));
      return;
    }
    setState(() => _preview = const AsyncLoading());
    try {
      final preview = await ref.read(authRepositoryProvider).invitation(trimmed);
      if (!mounted) return;
      setState(() {
        _preview = AsyncData(preview);
        if (_name.text.isEmpty && preview.name != null) _name.text = preview.name!;
        if (_email.text.isEmpty && preview.email != null) _email.text = preview.email!;
      });
    } on ApiFailure catch (f, st) {
      if (mounted) setState(() => _preview = AsyncError(f, st));
    }
  }

  Future<void> _submit() async {
    setState(() => _failure = null);
    if (!(_form.currentState?.validate() ?? false)) {
      AuHaptics.error();
      return;
    }
    if (!_terms) {
      AuToast.show(context, context.l10n.acceptTermsRequired, tone: AuToastTone.warning);
      return;
    }
    _type = _hasCoach ? AccountType.client : AccountType.standalone;
    setState(() => _loading = true);
    try {
      await ref.read(authControllerProvider.notifier).register(
            name: _name.text.trim(),
            email: _email.text.trim(),
            password: _password.text,
            accountType: _type,
            inviteCode: _hasCoach ? _code.text.trim() : null,
          );
    } on ApiFailure catch (failure) {
      if (!mounted) return;
      setState(() => _failure = failure);
      if (failure.fieldErrors.isEmpty) showError(context, failure);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final au = context.au;
    return AuthLayout(
      title: l10n.signUpTitle,
      subtitle: l10n.signUpSubtitle,
      children: [
        Row(
          children: [
            Expanded(
              child: _TypeCard(
                key: const Key('type-coach'),
                icon: Icons.workspace_premium_outlined,
                title: l10n.haveCoach,
                subtitle: l10n.haveCoachHint,
                selected: _hasCoach,
                onTap: () => setState(() => _hasCoach = true),
              ),
            ),
            const SizedBox(width: AuSpacing.s3),
            Expanded(
              child: _TypeCard(
                key: const Key('type-solo'),
                icon: Icons.self_improvement_rounded,
                title: l10n.trainOnMyOwn,
                subtitle: l10n.trainOnMyOwnHint,
                selected: !_hasCoach,
                onTap: () => setState(() => _hasCoach = false),
              ),
            ),
          ],
        ),
        const SizedBox(height: AuSpacing.s6),
        Form(
          key: _form,
          child: AutofillGroup(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                AnimatedSize(
                  duration: AuMotion.base,
                  curve: AuMotion.easeOut,
                  child: _hasCoach
                      ? Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            AuTextField(
                              key: const Key('signup-code'),
                              controller: _code,
                              label: l10n.inviteCode,
                              hint: l10n.inviteCodeHint,
                              prefixIcon: Icons.key_rounded,
                              textCapitalization: TextCapitalization.characters,
                              textDirection: TextDirection.ltr,
                              onChanged: _onCodeChanged,
                              errorText: _failure?.fieldError('invite_code'),
                              validator: (v) => _hasCoach ? Validators.required(context, v) : null,
                            ),
                            const SizedBox(height: AuSpacing.s3),
                            _preview.when(
                              data: (p) => p == null ? const SizedBox.shrink() : InvitePreviewCard(preview: p, compact: true),
                              loading: () => const AuShimmer(child: AuSkeleton(height: 72, radius: AuRadius.lg)),
                              error: (e, _) => Text(
                                auFailureMessage(context, e),
                                style: au.text.caption.copyWith(color: au.danger),
                              ),
                            ),
                            const SizedBox(height: AuSpacing.s4),
                          ],
                        )
                      : const SizedBox(width: double.infinity),
                ),
                AuTextField(
                  key: const Key('signup-name'),
                  controller: _name,
                  label: context.core.name,
                  textInputAction: TextInputAction.next,
                  autofillHints: const [AutofillHints.name],
                  textCapitalization: TextCapitalization.words,
                  prefixIcon: Icons.person_outline_rounded,
                  errorText: _failure?.fieldError('name'),
                  validator: (v) => Validators.required(context, v),
                ),
                const SizedBox(height: AuSpacing.s4),
                AuTextField(
                  key: const Key('signup-email'),
                  controller: _email,
                  label: context.core.email,
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
                  key: const Key('signup-password'),
                  controller: _password,
                  label: context.core.password,
                  obscure: true,
                  autofillHints: const [AutofillHints.newPassword],
                  prefixIcon: Icons.lock_outline_rounded,
                  helperText: context.core.passwordTooShort,
                  errorText: _failure?.fieldError('password'),
                  validator: (v) => Validators.password(context, v),
                ),
                const SizedBox(height: AuSpacing.s4),
                InkWell(
                  borderRadius: BorderRadius.circular(AuRadius.sm),
                  onTap: () => setState(() => _terms = !_terms),
                  child: Row(
                    children: [
                      Checkbox(key: const Key('signup-terms'), value: _terms, onChanged: (v) => setState(() => _terms = v ?? false)),
                      Expanded(child: Text(l10n.acceptTerms, style: au.text.bodySmall.copyWith(color: au.textSecondary))),
                    ],
                  ),
                ),
                const SizedBox(height: AuSpacing.s4),
                AuButton(key: const Key('signup-submit'), label: l10n.createAccount, loading: _loading, onPressed: _submit),
                const SizedBox(height: AuSpacing.s6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(l10n.alreadyHaveAccount, style: au.text.bodySmall.copyWith(color: au.textSecondary)),
                    TextButton(onPressed: () => context.go(Routes.signIn), child: Text(l10n.signIn)),
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

class _TypeCard extends StatelessWidget {
  const _TypeCard({
    super.key,
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.selected,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Semantics(
      selected: selected,
      button: true,
      child: GestureDetector(
        onTap: () {
          AuHaptics.selection();
          onTap();
        },
        child: AnimatedContainer(
          duration: AuMotion.base,
          curve: AuMotion.easeOut,
          constraints: const BoxConstraints(minHeight: 150),
          padding: const EdgeInsetsDirectional.all(AuSpacing.s4),
          decoration: BoxDecoration(
            color: selected ? au.accent.withValues(alpha: 0.10) : au.surface,
            borderRadius: BorderRadius.circular(AuRadius.lg),
            border: Border.all(color: selected ? au.accent : au.border, width: selected ? 1.5 : 1),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(icon, color: selected ? au.accent : au.textSecondary),
                  const Spacer(),
                  Icon(selected ? Icons.radio_button_checked_rounded : Icons.radio_button_off_rounded,
                      size: 20, color: selected ? au.accent : au.borderStrong),
                ],
              ),
              const SizedBox(height: AuSpacing.s4),
              Text(title, style: au.text.title),
              const SizedBox(height: AuSpacing.s1),
              Text(subtitle, style: au.text.caption.copyWith(color: au.textSecondary)),
            ],
          ),
        ),
      ),
    );
  }
}
