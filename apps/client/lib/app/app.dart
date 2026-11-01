import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../l10n/gen/client_localizations.dart';
import 'app_lifecycle.dart';
import 'biometric_lock.dart';
import 'env.dart';
import 'router.dart';

class AurumClientApp extends ConsumerStatefulWidget {
  const AurumClientApp({super.key});

  @override
  ConsumerState<AurumClientApp> createState() => _AurumClientAppState();
}

class _AurumClientAppState extends ConsumerState<AurumClientApp> {
  @override
  void initState() {
    super.initState();
    ref.read(hapticsProvider);
    unawaited(ref.read(authControllerProvider.notifier).restore());
  }

  @override
  Widget build(BuildContext context) {
    final router = ref.watch(routerProvider);
    final locale = ref.watch(effectiveLocaleProvider);
    final themeMode = ref.watch(themeModeProvider);
    final tenant = ref.watch(authControllerProvider.select((s) => s.activeTenant));
    final accent = AppEnv.tenantBranding ? AuTheme.parseHex(tenant?.brandColor) : null;

    return MaterialApp.router(
      title: 'Aurum',
      debugShowCheckedModeBanner: false,
      routerConfig: router,
      locale: locale,
      supportedLocales: AuLocales.supported,
      localizationsDelegates: const [
        ClientL10n.delegate,
        AuL10n.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      themeMode: themeMode,
      theme: AuThemeData.light(accent: accent, locale: locale),
      darkTheme: AuThemeData.dark(accent: accent, locale: locale),
      builder: (context, child) => AppLifecycleScope(child: BiometricLockGate(child: child ?? const SizedBox.shrink())),
    );
  }
}
