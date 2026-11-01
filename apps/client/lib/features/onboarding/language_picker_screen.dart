import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../shared/l10n_ext.dart';

/// Language picker (onboarding and settings). Switching flips layout
/// direction immediately and updates the account locale when signed in.
class LanguagePickerScreen extends ConsumerWidget {
  const LanguagePickerScreen({super.key, this.fromSettings = false});

  final bool fromSettings;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final selected = ref.watch(localeControllerProvider);
    final core = context.core;
    final options = <(Locale?, String, String)>[
      (null, core.languageSystem, ''),
      (const Locale('ar'), core.languageArabic, 'Arabic'),
      (const Locale('en'), core.languageEnglish, 'English'),
      (const Locale('fr'), core.languageFrench, 'French'),
      (const Locale('es'), core.languageSpanish, 'Spanish'),
    ];

    Future<void> choose(Locale? locale) async {
      await ref.read(localeControllerProvider.notifier).set(locale);
      final auth = ref.read(authControllerProvider);
      final code = ref.read(effectiveLocaleProvider).languageCode;
      unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.languageChanged(locale: code)));
      if (auth.isSignedIn) {
        try {
          await ref.read(authControllerProvider.notifier).updateUser({'locale': code});
        } on ApiFailure {
          // Applied locally; the account preference syncs next time.
        }
      }
    }

    return Scaffold(
      appBar: AppBar(title: Text(context.l10n.chooseLanguage)),
      body: ListView(
        padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
        children: [
          Text(context.l10n.languageHint, style: au.text.body.copyWith(color: au.textSecondary)),
          const SizedBox(height: AuSpacing.s5),
          AuCard(
            padding: EdgeInsets.zero,
            child: Column(
              children: [
                for (final (locale, label, english) in options) ...[
                  AuListRow(
                    title: label,
                    subtitle: english.isEmpty ? null : english,
                    trailing: Icon(
                      selected?.languageCode == locale?.languageCode ? Icons.check_circle_rounded : Icons.circle_outlined,
                      color: selected?.languageCode == locale?.languageCode ? au.accent : au.borderStrong,
                    ),
                    onTap: () => choose(locale),
                  ),
                  if (locale != options.last.$1) const Divider(indent: AuSpacing.s4),
                ],
              ],
            ),
          ),
          const SizedBox(height: AuSpacing.s6),
          if (!fromSettings) AuButton(label: context.core.done, onPressed: () => context.pop()),
        ],
      ),
    );
  }
}
