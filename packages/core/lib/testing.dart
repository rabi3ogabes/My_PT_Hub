/// Test support for apps built on aurum_core (golden and widget tests).
///
/// Import only from `test/` code.
library;

import 'dart:io';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import 'src/design/au_theme_data.dart';
import 'src/l10n/gen/au_localizations.dart';

bool _fontsLoaded = false;

/// Loads the bundled Manrope / IBM Plex Sans Arabic fonts and Material Icons
/// so golden images render real glyphs instead of the test font.
Future<void> loadAuFontsForTests() async {
  if (_fontsLoaded) return;
  final fontsDir = '${_corePackageRoot()}/assets/fonts';

  Future<void> load(String family, List<String> files) async {
    final loader = FontLoader(family);
    for (final f in files) {
      final file = File('$fontsDir/$f');
      if (file.existsSync()) {
        loader.addFont(Future.value(ByteData.sublistView(file.readAsBytesSync())));
      }
    }
    await loader.load();
  }

  const manrope = ['Manrope-Regular.ttf', 'Manrope-Medium.ttf', 'Manrope-SemiBold.ttf', 'Manrope-Bold.ttf', 'Manrope-ExtraBold.ttf'];
  const plex = [
    'IBMPlexSansArabic-Regular.ttf',
    'IBMPlexSansArabic-Medium.ttf',
    'IBMPlexSansArabic-SemiBold.ttf',
    'IBMPlexSansArabic-Bold.ttf',
  ];
  await load('packages/aurum_core/Manrope', manrope);
  await load('packages/aurum_core/IBM Plex Sans Arabic', plex);
  await load('Manrope', manrope);
  await load('IBM Plex Sans Arabic', plex);

  final flutterRoot = Platform.environment['FLUTTER_ROOT'];
  if (flutterRoot != null) {
    final icons = File('$flutterRoot/bin/cache/artifacts/material_fonts/MaterialIcons-Regular.otf');
    if (icons.existsSync()) {
      final loader = FontLoader('MaterialIcons')..addFont(Future.value(ByteData.sublistView(icons.readAsBytesSync())));
      await loader.load();
    }
  }
  _fontsLoaded = true;
}

/// Resolves the aurum_core package directory from the test's
/// `.dart_tool/package_config.json` (tests run with the package as cwd).
String _corePackageRoot() {
  final configFile = File('${Directory.current.path}/.dart_tool/package_config.json');
  final config = jsonDecode(configFile.readAsStringSync()) as Map<String, dynamic>;
  for (final package in (config['packages'] as List).cast<Map<String, dynamic>>()) {
    if (package['name'] == 'aurum_core') {
      final root = configFile.parent.uri.resolve(package['rootUri'] as String);
      return Directory.fromUri(root).path.replaceAll(RegExp(r'/$'), '');
    }
  }
  return Directory.current.path;
}

/// Wraps [child] in a themed, localized MaterialApp for widget tests.
Widget auTestApp({
  required Widget child,
  Locale locale = const Locale('en'),
  Brightness brightness = Brightness.dark,
  List<LocalizationsDelegate<dynamic>> extraDelegates = const [],
}) {
  return MaterialApp(
    debugShowCheckedModeBanner: false,
    locale: locale,
    supportedLocales: AuL10n.supportedLocales,
    localizationsDelegates: [
      ...extraDelegates,
      AuL10n.delegate,
      GlobalMaterialLocalizations.delegate,
      GlobalWidgetsLocalizations.delegate,
      GlobalCupertinoLocalizations.delegate,
    ],
    theme: brightness == Brightness.dark ? AuThemeData.dark(locale: locale) : AuThemeData.light(locale: locale),
    home: child,
  );
}
