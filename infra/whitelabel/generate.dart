// Aurum white-label generator.
//
//   dart run infra/whitelabel/generate.dart <slug> [--strict] [--dry-run] [--out=<dir>]
//   dart run infra/whitelabel/generate.dart --all
//
// Reads infra/whitelabel/tenants/<slug>.json and writes, relative to the repo root:
//   apps/client/config/flavors/<flavor>.json            --dart-define-from-file values
//   apps/client/flutter_launcher_icons-<flavor>.yaml     icon config (flutter_launcher_icons)
//   apps/client/flutter_native_splash-<flavor>.yaml      splash config (flutter_native_splash)
//   apps/client/ios/Flutter/<flavor>-Debug.xcconfig      bundle id, display name, icon set
//   apps/client/ios/Flutter/<flavor>-Release.xcconfig
//   apps/client/android/app/whitelabel-flavors.gradle    productFlavors for ALL tenants
//   store/whitelabel/<slug>/<locale>/*.txt               store metadata skeleton
//   store/whitelabel/<slug>/publishing_checklist.md      steps for the chosen publishing mode
//
// No package dependencies: runs with the plain Dart SDK.

import 'dart:convert';
import 'dart:io';

const supportedLocales = {'en', 'ar', 'fr', 'es'};
const knownFeatures = {
  'workouts', 'nutrition', 'habits', 'checkins', 'messaging', 'community', 'calendar',
  'shop', 'ai_food_photo', 'ai_coach_chat', 'health', 'marketplace', 'challenges',
};

final _slugRe = RegExp(r'^[a-z0-9]+(?:-[a-z0-9]+)*$');
final _flavorRe = RegExp(r'^[a-z][a-z0-9]{1,30}$');
final _bundleRe = RegExp(r'^[a-zA-Z][a-zA-Z0-9]*(\.[a-zA-Z][a-zA-Z0-9_]*){2,}$');
final _hexRe = RegExp(r'^#[0-9A-Fa-f]{6}$');
final _ulidRe = RegExp(r'^[0-9A-HJKMNP-TV-Z]{26}$');

class GenerationError implements Exception {
  GenerationError(this.messages);
  final List<String> messages;
  @override
  String toString() => messages.map((m) => '  ✗ $m').join('\n');
}

Directory findRepoRoot() {
  var dir = File(Platform.script.toFilePath()).parent;
  while (true) {
    if (File('${dir.path}/CLAUDE.md').existsSync() && Directory('${dir.path}/infra').existsSync()) {
      return dir;
    }
    final parent = dir.parent;
    if (parent.path == dir.path) return Directory.current;
    dir = parent;
  }
}

Future<void> main(List<String> args) async {
  final root = findRepoRoot();
  final strict = args.contains('--strict');
  final dryRun = args.contains('--dry-run');
  // --out=<dir> writes the generated tree somewhere else (CI previews, tests).
  final outArg = args.firstWhere((a) => a.startsWith('--out='), orElse: () => '');
  final outRoot = outArg.isEmpty ? root.path : outArg.substring(6);
  final positional = args.where((a) => !a.startsWith('--')).toList();
  final tenantsDir = Directory('${root.path}/infra/whitelabel/tenants');

  final slugs = args.contains('--all')
      ? tenantsDir
          .listSync()
          .whereType<File>()
          .where((f) => f.path.endsWith('.json'))
          .map((f) => f.uri.pathSegments.last.replaceAll('.json', ''))
          .toList()
      : positional;

  if (slugs.isEmpty) {
    stderr.writeln('usage: dart run infra/whitelabel/generate.dart <slug> [--strict] [--dry-run] | --all');
    exit(64);
  }

  final allTenants = <Map<String, dynamic>>[];
  for (final f in tenantsDir.listSync().whereType<File>().where((f) => f.path.endsWith('.json'))) {
    allTenants.add(jsonDecode(f.readAsStringSync()) as Map<String, dynamic>);
  }

  var failed = false;
  for (final slug in slugs) {
    final file = File('${tenantsDir.path}/$slug.json');
    if (!file.existsSync()) {
      stderr.writeln('✗ ${file.path} not found');
      failed = true;
      continue;
    }
    final tenant = jsonDecode(file.readAsStringSync()) as Map<String, dynamic>;
    try {
      final warnings = validate(tenant, slug, root, strict: strict);
      for (final w in warnings) {
        stdout.writeln('  ! $w');
      }
      final outputs = render(tenant, allTenants, root);
      for (final entry in outputs.entries) {
        final out = File('$outRoot/${entry.key}');
        if (dryRun) {
          stdout.writeln('  would write ${entry.key}');
          continue;
        }
        out.parent.createSync(recursive: true);
        out.writeAsStringSync(entry.value);
        stdout.writeln('  wrote ${entry.key}');
      }
      stdout.writeln('✓ ${tenant['app_name']} ($slug) → flavor "${tenant['flavor']}"');
      stdout.writeln(nextSteps(tenant));
    } on GenerationError catch (e) {
      stderr.writeln('✗ $slug is invalid:\n$e');
      failed = true;
    }
  }
  if (failed) exit(1);
}

List<String> validate(Map<String, dynamic> t, String slug, Directory root, {required bool strict}) {
  final errors = <String>[];
  final warnings = <String>[];

  String? str(String path) {
    dynamic node = t;
    for (final part in path.split('.')) {
      if (node is Map && node.containsKey(part)) {
        node = node[part];
      } else {
        return null;
      }
    }
    return node is String ? node : null;
  }

  void require(String path, RegExp? re, String hint) {
    final v = str(path);
    if (v == null || v.isEmpty) {
      errors.add('$path is required ($hint)');
    } else if (re != null && !re.hasMatch(v)) {
      errors.add('$path "$v" is invalid ($hint)');
    }
  }

  require('slug', _slugRe, 'kebab-case, must match the file name');
  if (t['slug'] != slug) errors.add('slug "${t['slug']}" does not match file name "$slug.json"');
  require('flavor', _flavorRe, 'lowercase letters/digits, starts with a letter (Android flavor + iOS scheme name)');
  require('tenant_id', _ulidRe, 'ULID of the Studio tenant');
  require('app_name', null, 'store/app display name');
  require('ios.bundle_id', _bundleRe, 'reverse-DNS, e.g. coach.aurum.wl.noorfitness');
  require('android.package', _bundleRe, 'reverse-DNS application id');
  require('brand.primary', _hexRe, '#RRGGBB');
  require('brand.accent', _hexRe, '#RRGGBB');
  require('default_locale', null, 'one of ${supportedLocales.join(', ')}');
  require('deep_link_domain', null, 'host serving apple-app-site-association / assetlinks.json');
  require('privacy_url', null, 'https URL of the privacy policy');

  final appName = str('app_name') ?? '';
  if (appName.length > 30) errors.add('app_name is ${appName.length} chars; App Store name limit is 30');
  if (appName.length > 12) warnings.add('app_name "$appName" may be truncated under the home-screen icon (>12 chars); set short_name');

  final mode = str('publishing.mode');
  if (mode != 'aurum_account' && mode != 'own_account') {
    errors.add('publishing.mode must be "aurum_account" or "own_account"');
  }
  if (mode == 'own_account' && (str('ios.team_id') ?? '').isEmpty) {
    errors.add('ios.team_id is required when publishing under the coach\'s own Apple account');
  }

  final locales = (t['locales'] as List?)?.cast<String>() ?? const [];
  if (locales.isEmpty) errors.add('locales must list at least one of $supportedLocales');
  for (final l in locales) {
    if (!supportedLocales.contains(l)) errors.add('locale "$l" is not supported');
  }
  if (!locales.contains(t['default_locale'])) errors.add('default_locale must be one of locales');

  final features = (t['features'] as Map?)?.cast<String, dynamic>() ?? const {};
  for (final k in features.keys) {
    if (!knownFeatures.contains(k)) warnings.add('unknown feature toggle "$k" (ignored by the app)');
    if (features[k] is! bool) errors.add('features.$k must be true/false');
  }

  final privacy = str('privacy_url') ?? '';
  if (privacy.isNotEmpty && !privacy.startsWith('https://')) errors.add('privacy_url must be https');

  for (final key in ['icon', 'icon_foreground', 'splash_logo']) {
    final p = str('assets.$key');
    if (p == null) {
      (key == 'icon' ? errors : warnings).add('assets.$key is not set');
      continue;
    }
    if (!File('${root.path}/$p').existsSync()) {
      (strict ? errors : warnings).add('assets.$key file not found: $p');
    }
  }

  if (errors.isNotEmpty) throw GenerationError(errors);
  return warnings;
}

String _jsonPretty(Object o) => '${const JsonEncoder.withIndent('  ').convert(o)}\n';

String _displayName(Map<String, dynamic> t) {
  final suffix = (t['publishing'] as Map)['display_suffix'] as String?;
  final name = t['app_name'] as String;
  if ((t['publishing'] as Map)['mode'] == 'aurum_account' && suffix != null && suffix.isNotEmpty) {
    final full = '$name — $suffix';
    if (full.length <= 30) return full;
  }
  return name;
}

Map<String, String> render(Map<String, dynamic> t, List<Map<String, dynamic>> all, Directory root) {
  final flavor = t['flavor'] as String;
  final slug = t['slug'] as String;
  final brand = (t['brand'] as Map).cast<String, dynamic>();
  final assets = (t['assets'] as Map).cast<String, dynamic>();
  final ios = (t['ios'] as Map).cast<String, dynamic>();
  final features = (t['features'] as Map?)?.cast<String, dynamic>() ?? {};
  final locales = (t['locales'] as List).cast<String>();
  final shortName = (t['short_name'] as String?) ?? t['app_name'] as String;
  final header = 'GENERATED by infra/whitelabel/generate.dart from infra/whitelabel/tenants/$slug.json — do not edit.';
  final assetPath = (String key) => '../../${assets[key]}';
  final out = <String, String>{};

  // 1. Dart defines for `flutter build --flavor <flavor> --dart-define-from-file=...`
  out['apps/client/config/flavors/$flavor.json'] = _jsonPretty({
    'FLAVOR': flavor,
    'WHITE_LABEL': true,
    'TENANT_ID': t['tenant_id'],
    'TENANT_SLUG': slug,
    'APP_NAME': t['app_name'],
    'APP_SHORT_NAME': shortName,
    'API_BASE_URL': t['api_base_url'] ?? 'https://api.aurum.coach/api/v1',
    'REVERB_HOST': t['reverb_host'] ?? 'ws.aurum.coach',
    'REVERB_PORT': 443,
    'REVERB_SCHEME': 'https',
    'DEEP_LINK_DOMAIN': t['deep_link_domain'],
    'DEFAULT_LOCALE': t['default_locale'],
    'SUPPORTED_LOCALES': locales.join(','),
    'BRAND_PRIMARY': brand['primary'],
    'BRAND_ACCENT': brand['accent'],
    'BRAND_BACKGROUND_DARK': brand['background_dark'] ?? '#0B0B0F',
    'BRAND_BACKGROUND_LIGHT': brand['background_light'] ?? '#FAF8F4',
    'FONT_LATIN': brand['font_latin'] ?? 'Manrope',
    'FONT_ARABIC': brand['font_arabic'] ?? 'IBM Plex Sans Arabic',
    'SUPPORT_EMAIL': t['support_email'] ?? 'support@aurum.coach',
    'PRIVACY_URL': t['privacy_url'],
    for (final f in knownFeatures) 'FEATURE_${f.toUpperCase()}': features[f] ?? true,
  });

  // 2. flutter_launcher_icons
  final iconBg = brand['icon_background'] ?? brand['background_dark'] ?? '#0B0B0F';
  out['apps/client/flutter_launcher_icons-$flavor.yaml'] = '''# $header
flutter_launcher_icons:
  image_path: "${assetPath('icon')}"
  android: "ic_launcher"
  min_sdk_android: 24
  adaptive_icon_background: "$iconBg"
  adaptive_icon_foreground: "${assets['icon_foreground'] != null ? assetPath('icon_foreground') : assetPath('icon')}"
  adaptive_icon_monochrome: "${assets['icon_foreground'] != null ? assetPath('icon_foreground') : assetPath('icon')}"
  ios: true
  remove_alpha_ios: true
  background_color_ios: "$iconBg"
''';

  // 3. flutter_native_splash
  final splash = assets['splash_logo'] != null ? assetPath('splash_logo') : assetPath('icon');
  out['apps/client/flutter_native_splash-$flavor.yaml'] = '''# $header
flutter_native_splash:
  color: "${brand['background_light'] ?? '#FAF8F4'}"
  color_dark: "${brand['background_dark'] ?? '#0B0B0F'}"
  image: "$splash"
  image_dark: "$splash"
  android_12:
    color: "${brand['background_light'] ?? '#FAF8F4'}"
    color_dark: "${brand['background_dark'] ?? '#0B0B0F'}"
    image: "$splash"
    image_dark: "$splash"
    icon_background_color: "$iconBg"
    icon_background_color_dark: "$iconBg"
  fullscreen: false
  web: false
''';

  // 4. iOS xcconfigs (Debug-/Release-/Profile-<flavor> build configurations; see Fastfile ensure_ios_flavor)
  final display = shortName; // home-screen label; the store title carries the full name
  for (final cfg in ['Debug', 'Release']) {
    final podsCfg = cfg.toLowerCase();
    out['apps/client/ios/Flutter/$flavor-$cfg.xcconfig'] = '''// $header
#include? "Pods/Target Support Files/Pods-Runner/Pods-Runner.$podsCfg-$flavor.xcconfig"
#include "Generated.xcconfig"

PRODUCT_BUNDLE_IDENTIFIER = ${ios['bundle_id']}
DISPLAY_NAME = $display
ASSETCATALOG_COMPILER_APPICON_NAME = AppIcon-$flavor
APP_ASSOCIATED_DOMAIN = applinks:${t['deep_link_domain']}
${ios['team_id'] != null ? 'DEVELOPMENT_TEAM = ${ios['team_id']}' : '// DEVELOPMENT_TEAM inherited from the Aurum account'}
''';
  }

  // 5. Android productFlavors for every tenant (so builds of one flavor never drop another).
  final flavors = StringBuffer();
  final sorted = [...all]..sort((a, b) => (a['flavor'] as String).compareTo(b['flavor'] as String));
  for (final x in sorted) {
    final xa = (x['android'] as Map).cast<String, dynamic>();
    final xb = (x['brand'] as Map).cast<String, dynamic>();
    final xName = ((x['short_name'] as String?) ?? x['app_name'] as String).replaceAll('"', r'\"');
    flavors.writeln('''        ${x['flavor']} {
            dimension "env"
            applicationId "${xa['package']}"
            resValue "string", "app_name", "$xName"
            resValue "color", "brand_primary", "${xb['primary']}"
            manifestPlaceholders += [deepLinkHost: "${x['deep_link_domain']}"]
        }''');
  }
  out['apps/client/android/app/whitelabel-flavors.gradle'] = '''// $header
// Applied from app/build.gradle(.kts):  apply(from = "whitelabel-flavors.gradle")
// Uses the same "env" dimension as the dev / staging / prod flavors.
android {
    productFlavors {
${flavors.toString().trimRight()}
    }
}
''';

  // 6. Store metadata skeleton
  final store = (t['store'] as Map?)?.cast<String, dynamic>() ?? {};
  final coach = t['coach_name'] as String? ?? t['app_name'] as String;
  for (final locale in locales) {
    final dir = 'store/whitelabel/$slug/$locale';
    final copy = _storeCopy(locale, t['app_name'] as String, coach);
    final overrides = (store[locale] as Map?)?.cast<String, dynamic>() ?? {};
    out['$dir/title.txt'] = '${_clip(overrides['title'] as String? ?? _displayName(t), 30)}\n';
    out['$dir/subtitle.txt'] = '${_clip(overrides['subtitle'] as String? ?? copy['subtitle']!, 30)}\n';
    out['$dir/short_description.txt'] = '${_clip(overrides['short_description'] as String? ?? copy['short']!, 80)}\n';
    out['$dir/keywords.txt'] = '${_clip(_keywords(overrides['keywords'] as String? ?? copy['keywords']!, _displayName(t)), 100)}\n';
    out['$dir/full_description.txt'] = '${_clip(overrides['full_description'] as String? ?? copy['full']!, 4000)}\n';
    out['$dir/privacy_url.txt'] = '${t['privacy_url']}\n';
  }
  out['store/whitelabel/$slug/publishing_checklist.md'] = _checklist(t);
  return out;
}

/// iOS keyword field: comma-separated, no spaces, no words already in the title.
String _keywords(String raw, String title) {
  final titleWords = title.toLowerCase().split(RegExp(r'[\s\-—:,]+')).where((w) => w.isNotEmpty).toSet();
  final kept = raw
      .split(',')
      .map((k) => k.trim().replaceAll(' ', ''))
      .where((k) => k.isNotEmpty && !titleWords.contains(k.toLowerCase()))
      .toList();
  final out = StringBuffer();
  for (final k in kept) {
    final next = out.isEmpty ? k : '${out.toString()},$k';
    if (next.length > 100) break;
    out
      ..clear()
      ..write(next);
  }
  return out.toString();
}

String _clip(String s, int max) => s.length <= max ? s : s.substring(0, max).trimRight();

Map<String, String> _storeCopy(String locale, String app, String coach) {
  switch (locale) {
    case 'ar':
      return {
        'subtitle': 'برنامجك مع $coach',
        'short': 'تمارين وتغذية ومتابعة يومية مع مدربك $coach في تطبيق واحد.',
        'keywords': 'مدرب,شخصي,تمارين,لياقة,تغذية,رجيم,دايت,جيم,عادات,وزن,متابعة,برنامج,تخسيس,عضلات,سعرات,صحي',
        'full': 'تطبيق $app هو مساحتك الخاصة مع $coach.\nبرنامجك وتغذيتك ومتابعتك الأسبوعية في مكان واحد.\n\nسجّل تمارينك بسرعة، حتى من دون إنترنت.\nتابع وجباتك بالباركود أو من خطتك بنقرة واحدة.\nأرسل متابعتك الأسبوعية وصور تقدّمك بخصوصية تامة.\nتواصل مع مدربك مباشرة بالرسائل والملاحظات الصوتية.\n\nحمّل التطبيق وادخل برمز الدعوة من مدربك.',
      };
    case 'fr':
      return {
        'subtitle': 'Votre coaching avec $coach',
        'short': 'Entraînements, nutrition et suivi avec $coach, réunis dans une seule app.',
        'keywords': 'coach,musculation,fitness,nutrition,programme,sport,régime,habitudes,suivi,séance,calories,minceur,poids',
        'full': '$app, c\'est votre espace privé avec $coach.\nProgramme, nutrition et bilans hebdomadaires au même endroit.\n\nNotez vos séances en quelques secondes, même hors ligne.\nSuivez vos repas par code-barres ou depuis votre plan en un geste.\nEnvoyez votre bilan et vos photos de progression en toute confidentialité.\nÉchangez avec votre coach par messages et notes vocales.\n\nTéléchargez l\'app et connectez-vous avec le code d\'invitation de votre coach.',
      };
    case 'es':
      return {
        'subtitle': 'Tu coaching con $coach',
        'short': 'Entrenos, nutrición y seguimiento con $coach, todo en una sola app.',
        'keywords': 'entrenador,gimnasio,fitness,nutrición,rutina,dieta,hábitos,progreso,ejercicio,plan,calorías,pesas,peso',
        'full': '$app es tu espacio privado con $coach.\nTu programa, tu nutrición y tus revisiones semanales en un solo lugar.\n\nRegistra tus entrenos en segundos, incluso sin conexión.\nAnota tus comidas con el código de barras o desde tu plan con un toque.\nEnvía tu revisión semanal y tus fotos de progreso con total privacidad.\nHabla con tu entrenador por mensajes y notas de voz.\n\nDescarga la app y entra con el código de invitación de tu entrenador.',
      };
    default:
      return {
        'subtitle': 'Coaching with $coach',
        'short': 'Workouts, nutrition and check-ins with $coach, together in one app.',
        'keywords': 'trainer,personal,workout,fitness,nutrition,meal,gym,habits,progress,checkin,plan,diet,calorie,weight',
        'full': '$app is your private space with $coach.\nYour program, nutrition and weekly check-ins in one place.\n\nLog workouts in seconds, even offline.\nTrack meals by barcode or straight from your plan in one tap.\nSend your weekly check-in and progress photos privately.\nMessage your coach directly, including voice notes.\n\nDownload the app and sign in with the invite code from your coach.',
      };
  }
}

String _checklist(Map<String, dynamic> t) {
  final mode = (t['publishing'] as Map)['mode'];
  final slug = t['slug'];
  final flavor = t['flavor'];
  final common = '''
## Build
- [ ] `dart run infra/whitelabel/generate.dart $slug --strict` passes (all assets present)
- [ ] Firebase iOS + Android apps created for `${(t['ios'] as Map)['bundle_id']}` / `${(t['android'] as Map)['package']}`; configs added to the `WL_FIREBASE_CONFIGS_JSON` secret under key `$slug`
- [ ] `${t['deep_link_domain']}` serves `/.well-known/apple-app-site-association` and `/.well-known/assetlinks.json` with the new ids
- [ ] Run **Mobile release → whitelabel** with tenant `$slug` (fastlane `build_whitelabel tenant:$slug`) — builds flavor `$flavor`
- [ ] Smoke test from TestFlight / Play internal: sign in with an invite code, Arabic RTL, push notification, offline workout

## Store
- [ ] Listing copy reviewed in `store/whitelabel/$slug/*` (title ≤30, subtitle ≤30, short ≤80, keywords ≤100)
- [ ] Screenshots generated from the branded build (8 per locale)
- [ ] Privacy policy live at ${t['privacy_url']}; privacy labels/data safety copied from `store/privacy/`
- [ ] Review notes include a demo invite code and explain the coach-specific content (guideline 4.2.6)
''';
  if (mode == 'own_account') {
    return '''# Publishing checklist — ${t['app_name']} (coach's own developer account)

Full white label: the app is published by the coach's business. Aurum builds and uploads it.

## Accounts (coach)
- [ ] Apple Developer Program enrollment as an **organization** (requires a D-U-N-S number)
- [ ] Invite the Aurum release bot as **App Manager** in App Store Connect; create an App Store Connect API key and share it via the secure upload link
- [ ] Google Play Console developer account (organization verification) and invite the Aurum service account with release permissions
- [ ] Team id `${(t['ios'] as Map)['team_id']}` recorded in the tenant JSON; match branch `$slug` created
$common''';
  }
  return '''# Publishing checklist — ${t['app_name']} (published by Aurum)

Mode 1 (included in Studio): the app ships under Aurum's developer account as "${_displayName(t)}".
$common''';
}

String nextSteps(Map<String, dynamic> t) => '''
  next:
    cd apps/client
    dart run flutter_launcher_icons            # processes every flutter_launcher_icons-<flavor>.yaml
    dart run flutter_native_splash:create --path=flutter_native_splash-${t['flavor']}.yaml --flavor=${t['flavor']}
    flutter run --flavor ${t['flavor']} --dart-define-from-file=config/flavors/${t['flavor']}.json
''';
