import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';

import '../onboarding/splash_screen.dart';

/// Shared luxury layout for auth screens: monogram, display title, subtitle,
/// then the form. Scrolls with the keyboard.
class AuthLayout extends StatelessWidget {
  const AuthLayout({super.key, required this.title, required this.children, this.subtitle, this.header});

  final String title;
  final String? subtitle;
  final Widget? header;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Scaffold(
      appBar: AppBar(backgroundColor: Colors.transparent),
      extendBodyBehindAppBar: true,
      body: DecoratedBox(
        decoration: BoxDecoration(
          gradient: RadialGradient(
            center: const AlignmentDirectional(-0.8, -1).resolve(Directionality.of(context)),
            radius: 1.1,
            colors: [au.accent.withValues(alpha: au.isDark ? 0.12 : 0.08), au.background],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 480),
              child: ListView(
                padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s6, AuSpacing.s10, AuSpacing.s6, AuSpacing.s8),
                children: [
                  header ?? const Align(alignment: AlignmentDirectional.centerStart, child: AurumMark(size: 44)),
                  const SizedBox(height: AuSpacing.s8),
                  Semantics(header: true, child: Text(title, style: au.text.h1)),
                  if (subtitle != null) ...[
                    const SizedBox(height: AuSpacing.s2),
                    Text(subtitle!, style: au.text.body.copyWith(color: au.textSecondary)),
                  ],
                  const SizedBox(height: AuSpacing.s8),
                  ...children,
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Basic client-side validators with localized messages.
abstract final class Validators {
  static final _email = RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$');

  static String? email(BuildContext context, String? value) {
    final v = value?.trim() ?? '';
    if (v.isEmpty) return AuL10n.of(context).fieldRequired;
    if (!_email.hasMatch(v)) return AuL10n.of(context).invalidEmail;
    return null;
  }

  static String? required(BuildContext context, String? value) =>
      (value?.trim().isEmpty ?? true) ? AuL10n.of(context).fieldRequired : null;

  static String? password(BuildContext context, String? value) {
    if (value == null || value.isEmpty) return AuL10n.of(context).fieldRequired;
    if (value.length < 8) return AuL10n.of(context).passwordTooShort;
    return null;
  }
}
