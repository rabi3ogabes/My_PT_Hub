/// Build flavour.
enum Flavor { dev, staging, prod }

/// Compile-time configuration passed with `--dart-define`.
///
/// ```
/// flutter run --dart-define=FLAVOR=dev --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
/// ```
abstract final class AppEnv {
  static const _flavor = String.fromEnvironment('FLAVOR', defaultValue: 'dev');
  static const _apiBaseUrl = String.fromEnvironment('API_BASE_URL');

  /// Web domain used for universal links (`https://{domain}/invite/{code}`).
  static const webDomain = String.fromEnvironment('WEB_DOMAIN', defaultValue: 'aurum.coach');

  /// Enable Firebase (requires google-services.json / GoogleService-Info.plist).
  static const firebaseEnabled = bool.fromEnvironment('FIREBASE_ENABLED');

  /// White-label hook: tint the accent with the coach's brand colour.
  static const tenantBranding = bool.fromEnvironment('TENANT_BRANDING');

  static Flavor get flavor => switch (_flavor) {
        'prod' || 'production' => Flavor.prod,
        'staging' => Flavor.staging,
        _ => Flavor.dev,
      };

  /// API base including `/api/v1`. Defaults per flavour when not provided.
  static String get apiBaseUrl {
    if (_apiBaseUrl.isNotEmpty) return _apiBaseUrl;
    return switch (flavor) {
      Flavor.prod => 'https://api.$webDomain/api/v1',
      Flavor.staging => 'https://api.staging.$webDomain/api/v1',
      Flavor.dev => 'http://localhost:8000/api/v1',
    };
  }

  static bool get isProd => flavor == Flavor.prod;

  /// Hosts accepted for universal links.
  static Set<String> get linkHosts => {webDomain, 'app.$webDomain', 'staging.$webDomain'};
}
