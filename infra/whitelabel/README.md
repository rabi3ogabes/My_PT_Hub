# Aurum white-label pipeline

Studio and Enterprise tenants can ship a branded client app. One JSON file per tenant
drives everything: flavor config, icons, splash, iOS/Android identifiers and a store
listing skeleton. Fastlane then builds and uploads to TestFlight and Google Play internal.

> **Default is the container model, not a separate binary.** Every coach's clients can use
> the Aurum client app and see their coach's branding after entering an invite code
> (ADR [0010](../../docs/adr/0010-ios-template-apps-container-model.md), App Store guideline 4.2.6).
> A separate binary is built only when the tenant has substantial branded content and
> publishes under its own developer account, or when Aurum publishes it under its own account
> with distinct functionality that review accepts.

## Files

```
infra/whitelabel/
├── generate.dart                 # pure Dart, no dependencies
├── tenants/
│   ├── noor-fitness.json         # EXAMPLE tenant (fictional) — copy for real tenants
│   └── assets/noor-fitness/      # icon.png (1024², no alpha), icon_foreground.png, splash_logo.png
└── README.md
```

## Tenant JSON

| Key | Required | Notes |
|---|---|---|
| `slug` | ✓ | kebab-case, equals the file name |
| `flavor` | ✓ | `^[a-z][a-z0-9]{1,30}$` — Android product flavor and iOS scheme name |
| `tenant_id` | ✓ | ULID of the tenant; the app is locked to this tenant (`X-Tenant`) |
| `app_name` / `short_name` | ✓ / – | store name (≤30) / home-screen label (≤12 recommended) |
| `coach_name` | – | used in generated store copy |
| `publishing.mode` | ✓ | `aurum_account` (Studio, one click, "Name — by Aurum") or `own_account` |
| `publishing.display_suffix` | – | e.g. `by Aurum` |
| `ios.bundle_id`, `ios.team_id` | ✓ / own_account | `team_id` required for `own_account` |
| `android.package` | ✓ | application id |
| `brand.*` | ✓ | `primary`, `accent` (hex), optional backgrounds, `icon_background`, fonts |
| `assets.*` | ✓ | repo-relative PNG paths; `--strict` fails if missing |
| `default_locale`, `locales` | ✓ | subset of `en, ar, fr, es` |
| `features.*` | – | booleans → `FEATURE_*` dart-defines (also overridable via Remote Config) |
| `deep_link_domain` | ✓ | host serving `apple-app-site-association` / `assetlinks.json` |
| `privacy_url` | ✓ | https; auto-hosted on the coach profile domain |
| `store.<locale>.*` | – | overrides for `title`, `subtitle`, `short_description`, `keywords`, `full_description` |

## Generate

```bash
dart run infra/whitelabel/generate.dart noor-fitness --strict    # one tenant
dart run infra/whitelabel/generate.dart --all                     # every tenant (refreshes Android flavors)
dart run infra/whitelabel/generate.dart noor-fitness --dry-run    # list outputs only
dart run infra/whitelabel/generate.dart noor-fitness --out=/tmp/wl  # write elsewhere (preview)
make whitelabel tenant=noor-fitness
```

Outputs (repo-relative):

| File | Purpose |
|---|---|
| `apps/client/config/flavors/<flavor>.json` | `--dart-define-from-file` values (tenant lock, brand, features, endpoints) |
| `apps/client/flutter_launcher_icons-<flavor>.yaml` | per-flavor icons (`dart run flutter_launcher_icons`) |
| `apps/client/flutter_native_splash-<flavor>.yaml` | per-flavor splash (`dart run flutter_native_splash:create --flavor <flavor>`) |
| `apps/client/ios/Flutter/<flavor>-{Debug,Release}.xcconfig` | bundle id, display name, `AppIcon-<flavor>`, associated domain |
| `apps/client/android/app/whitelabel-flavors.gradle` | product flavors for **all** tenants (apply from `app/build.gradle.kts`) |
| `store/whitelabel/<slug>/<locale>/*.txt` | listing skeleton (limits enforced, title words removed from keywords) |
| `store/whitelabel/<slug>/publishing_checklist.md` | steps for the chosen publishing mode |

One-time app wiring (done once in `apps/client`, not per tenant):

- `android/app/build.gradle.kts`: `flavorDimensions += "env"` for dev/staging/prod and `apply(from = "whitelabel-flavors.gradle")`.
- `ios/Runner/Info.plist`: `CFBundleDisplayName = $(DISPLAY_NAME)`; entitlements use `$(APP_ASSOCIATED_DOMAIN)`.
- iOS build configurations and schemes per flavor are created by fastlane (`ensure_ios_flavor`, idempotent, via `xcodeproj`).

## Build & ship

```bash
cd infra && BUNDLE_GEMFILE=fastlane/Gemfile bundle exec fastlane build_whitelabel tenant:noor-fitness
```

or GitHub → Actions → **Mobile release** → `action: whitelabel`, `tenant: noor-fitness`.

The lane: generates config → icons + splash → writes Firebase configs from the
`WL_FIREBASE_CONFIGS_JSON` secret (`{"<slug>": {"google_services_json_b64", "google_service_info_plist_b64"}}`)
→ match signing (branch `<slug>` for `own_account`) → `flutter build ipa/appbundle --flavor <flavor>`
→ TestFlight + Play internal.

## Updating branded apps

- Content, copy and feature toggles change over the air (API + Firebase Remote Config); no rebuild.
- Every core release triggers `--all` regeneration and a rebuild of each white-label flavor so
  binaries never fall more than one release behind.
