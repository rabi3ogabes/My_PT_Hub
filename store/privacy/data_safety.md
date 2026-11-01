# Google Play Data safety — Aurum client & Aurum Coach

Answers for Play Console → App content → Data safety, mapped from the real data flows
(tables in [`docs/ERD.md`](../../docs/ERD.md), endpoints in [`docs/openapi.yaml`](../../docs/openapi.yaml)).
Keep in sync with [`app_privacy_labels.md`](app_privacy_labels.md).

## Overview answers

| Question | Answer |
|---|---|
| Does your app collect or share any of the required user data types? | **Yes** |
| Is all of the user data collected by your app encrypted in transit? | **Yes** — TLS 1.2+ everywhere; HSTS on all web origins |
| Do you provide a way for users to request that their data is deleted? | **Yes** — in app (Settings → Privacy → Delete account) and at `https://aurum.coach/{locale}/legal/delete-account` (web form for users who uninstalled) |
| Has your app been independently validated against a global security standard (MASA)? | Not yet — planned after the external pentest (see `docs/SECURITY.md`) |
| Data shared with third parties | **No sharing** in Play's sense: all recipients are service providers processing on Aurum's behalf (Play's "service provider" exemption), or transfers the user initiates (paying their coach through a gateway) |

## Data types — client app (`coach.aurum.client`)

"Collected" = sent off device. "Optional" = the user can use the app without providing it.

| Play category → type | Collected | Shared | Optional | Ephemeral | Purposes | Source in system |
|---|---|---|---|---|---|---|
| Personal info → **Name** | ✓ | – | Required | No | Account management, App functionality | `users.name` |
| Personal info → **Email address** | ✓ | – | Required | No | Account management, Developer communications | `users.email` |
| Personal info → **Phone number** | ✓ | – | Optional | No | App functionality (WhatsApp/SMS reminders) | `users.phone` |
| Personal info → **Other info** (date of birth, gender) | ✓ | – | Optional | No | App functionality (nutrition calculations) | `client_profiles` (DOB encrypted) |
| Health and fitness → **Health info** | ✓ | – | Optional | No | App functionality | body metrics, check-in answers, injuries/medical notes (encrypted), nutrition logs, Health Connect samples |
| Health and fitness → **Fitness info** | ✓ | – | Optional | No | App functionality, Analytics | workout sessions/sets, PRs, habits, steps |
| Financial info → **Purchase history** | ✓ | – | Optional | No | App functionality | orders, subscriptions, invoices |
| Messages → **Other in-app messages** | ✓ | – | Optional | No | App functionality | chat (encrypted at rest), check-in comments |
| Photos and videos → **Photos** | ✓ | – | Optional | No | App functionality | progress photos (private), food photos (AI), attachments |
| Photos and videos → **Videos** | ✓ | – | Optional | No | App functionality | form-check uploads, attachments |
| Audio → **Voice or sound recordings** | ✓ | – | Optional | No | App functionality | voice notes |
| Files and docs → **Files and docs** | ✓ | – | Optional | No | App functionality | check-in file answers, signed contracts |
| App activity → **App interactions** | ✓ | – | Required | No | Analytics, Personalization | Firebase Analytics (no advertising id) |
| App activity → **Other user-generated content** | ✓ | – | Optional | No | App functionality | community posts, comments, coach reviews |
| App info and performance → **Crash logs** | ✓ | – | Required | No | Analytics | Crashlytics, Sentry |
| App info and performance → **Diagnostics** | ✓ | – | Required | No | Analytics | Firebase Performance, app/OS version |
| Device or other IDs → **Device or other IDs** | ✓ | – | Required | No | App functionality (push) | FCM token |

**Not collected:** Location (approximate or precise), Contacts, Calendar (client app), Web browsing, Installed apps, SMS/call logs, Race and ethnicity, Political or religious beliefs, Sexual orientation, Credit score, Payment card numbers (entered on the gateway's hosted page or wallet sheet; Aurum never receives them), Advertising ID (`com.google.android.gms.permission.AD_ID` removed from the merged manifest).

## Data types — coach app (`coach.aurum.coach`)

All client rows apply to the coach's own account, plus:

| Play category → type | Collected | Shared | Optional | Purposes | Source |
|---|---|---|---|---|---|
| Personal info → **Address** | ✓ | – | Optional | App functionality (invoices, locations) | `tenants.legal`, `locations.address` |
| Financial info → **User payment info** | ✓ | – | Optional | App functionality (SaaS subscription; brand + last 4 only) | `saas_subscriptions` |
| Financial info → **Other financial info** | ✓ | – | Optional | App functionality, Analytics | revenue analytics, gateway status |
| Calendar → **Calendar events** | ✓ | – | Optional | App functionality (two-way Google/Outlook sync: busy blocks and bookings) | `integrations`, `events.external_id` |
| Personal info → **Other info** (prospects' name/email/phone captured by the coach) | ✓ | – | Optional | App functionality (CRM) | `leads` |

## Security practices declared

- Encryption at rest: database volumes and object storage encrypted; additionally application-level encryption (Laravel encrypted casts) for messages, 2FA secrets, date of birth, injuries, medical notes, gateway credentials, integration tokens and webhook secrets.
- Progress photos and attachments live in a private bucket and are only served through short-lived signed URLs (10 minutes) after a tenant + ownership check.
- Users can export all their data (`POST /me/export`) and delete their account (`DELETE /me`); deletion is completed within 30 days, backups roll off within 35 days.
- Health Connect: read permissions are requested per data type with an in-app rationale screen; data is used only for the user's and their coach's progress views and habit auto-completion, never for advertising (Health Connect policy).

## Health apps declaration (Play Console → Health apps)

Category: **Fitness / Nutrition and weight management / Health and fitness coaching**. Not a medical device; no diagnosis or treatment features. AI outputs are reviewed by a human coach before reaching clients; the app shows a "not medical advice" notice on AI form-check and nutrition features.
