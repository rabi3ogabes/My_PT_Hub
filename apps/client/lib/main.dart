import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:package_info_plus/package_info_plus.dart';

import 'app/app.dart';
import 'app/env.dart';
import 'features/logger/audio_cues.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await SystemChrome.setPreferredOrientations([DeviceOrientation.portraitUp]);
  SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(statusBarColor: Colors.transparent));

  final services = await AuServices.initialize(enabled: AppEnv.firebaseEnabled);
  FlutterError.onError = services.crash.recordFlutterError;
  PlatformDispatcher.instance.onError = (error, stack) {
    unawaited(services.crash.recordError(error, stack, fatal: true));
    return true;
  };

  final results = await Future.wait([AppPrefs.load(), PackageInfo.fromPlatform()]);
  final prefs = results[0] as AppPrefs;
  final package = results[1] as PackageInfo;
  final notifier = PluginLocalNotifier();
  await notifier.initialize();
  unawaited(services.remoteConfig.fetchAndActivate());

  runApp(
    ProviderScope(
      // Failed reads surface an error state with "Try again" instead of
      // retrying silently in the background.
      retry: (_, _) => null,
      overrides: [
        appPrefsProvider.overrideWithValue(prefs),
        servicesProvider.overrideWithValue(services),
        localNotifierProvider.overrideWithValue(notifier),
        apiConfigProvider.overrideWithValue(
          ApiConfig(baseUrl: AppEnv.apiBaseUrl, app: 'client', appVersion: '${package.version}+${package.buildNumber}'),
        ),
        audioCuesProvider.overrideWith((ref) {
          final cues = JustAudioCues(enabled: () => ref.read(appPrefsProvider).restSound);
          ref.onDispose(cues.dispose);
          return cues;
        }),
      ],
      child: const AurumClientApp(),
    ),
  );
}
