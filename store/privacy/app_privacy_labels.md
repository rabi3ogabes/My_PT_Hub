# App Store privacy "nutrition labels" — Aurum client & Aurum Coach

Answers for App Store Connect → App Privacy, derived from the actual data flows in the
codebase (tables in [`docs/ERD.md`](../../docs/ERD.md), endpoints in
[`docs/openapi.yaml`](../../docs/openapi.yaml), SDKs in `packages/core/pubspec.yaml`).
Re-check this file whenever a migration adds a personal-data column or an SDK is added.

## Global answers

| Question | Answer | Why |
|---|---|---|
| Do you or your third-party partners collect data from this app? | **Yes** | Accounts, coaching content, health logs |
| Is data used to **track** users (linked with third-party data for ads, or shared with data brokers)? | **No** | No advertising SDKs, no IDFA (`AdSupport` not linked), Firebase Analytics with ad personalization and IDFA collection disabled; no data sold or brokered. **No App Tracking Transparency prompt is shown.** |
| Privacy policy URL | `https://aurum.coach/{locale}/legal/privacy` | Per locale; white-label apps use the tenant's hosted policy |
| Account deletion in app | **Yes** — Settings → Privacy → Delete account (`DELETE /me`) | Guideline 5.1.1(v) |

## Data types — client app (`coach.aurum.client`)

All types below are **Linked to the user** (they belong to the signed-in account) and **Not used for tracking**.

| App Store category → type | Collected data (source) | Purposes declared |
|---|---|---|
| Contact Info → **Name** | `users.name` | App Functionality |
| Contact Info → **Email Address** | `users.email` | App Functionality, Developer's Communications (transactional email: receipts, reminders) |
| Contact Info → **Phone Number** | `users.phone` (optional), WhatsApp reminders opt-in | App Functionality |
| Health & Fitness → **Health** | body metrics (`body_metrics`), check-in answers (`checkin_responses.answers`), injuries / medical notes / PAR-Q (`client_profiles`, encrypted), nutrition targets and food/water logs, HealthKit samples (`health_samples`: steps, sleep, heart rate, HRV, weight, body fat) | App Functionality |
| Health & Fitness → **Fitness** | workout sessions and sets (`workout_sessions`, `session_sets`), personal records, habit logs | App Functionality, Analytics (aggregated compliance for the client's own coach) |
| Financial Info → **Purchase History** | orders, subscriptions and invoices for coaching purchases (`orders`, `client_subscriptions`, `invoices`) | App Functionality |
| User Content → **Photos or Videos** | progress photos (private disk, signed URLs), form-check videos, food photos for AI logging, chat attachments | App Functionality |
| User Content → **Audio Data** | voice notes in chat | App Functionality |
| User Content → **Emails or Text Messages** | in-app chat messages (`messages.body`, encrypted at rest) | App Functionality |
| User Content → **Other User Content** | community posts/comments, signed contracts and waivers, reviews of coaches | App Functionality |
| Identifiers → **User ID** | account ULID | App Functionality, Analytics |
| Identifiers → **Device ID** | FCM push token (`devices.push_token`) | App Functionality (push notifications) |
| Usage Data → **Product Interaction** | Firebase Analytics events (screen views, workout completed, food logged) without ad identifiers | Analytics, Product Personalization (streaks, in-app review timing) |
| Diagnostics → **Crash Data** | Firebase Crashlytics, Sentry | App Functionality, Analytics |
| Diagnostics → **Performance Data** | Firebase Performance (cold start, network latency) | Analytics |
| Diagnostics → **Other Diagnostic Data** | app version, OS version, sync queue errors | App Functionality |

**Not collected:** Precise or coarse **Location** (time zone only; coach city is typed by the coach), Contacts, Browsing History, Search History (searches are not stored), Sensitive Info (race, religion, sexual orientation — not requested; Ramadan mode is a user setting, not a profile attribute), Payment Info (card numbers are entered on the payment provider's page; Aurum never receives them), Advertising Data, Other Financial Info, Credit Info.

## Data types — coach app (`coach.aurum.coach`)

Everything in the client table applies to coaches' own accounts, **plus**:

| App Store category → type | Collected data (source) | Purposes declared |
|---|---|---|
| Financial Info → **Payment Info** | SaaS subscription card brand + last 4 (`saas_subscriptions.card_brand/last4`) returned by Stripe; full card data stays with Stripe | App Functionality |
| Financial Info → **Other Financial Info** | payout / gateway connection status, revenue analytics (`analytics_daily`), invoice legal details (`tenants.legal`) | App Functionality, Analytics |
| Contact Info → **Physical Address** | business address for invoices and locations (`tenants.legal`, `locations.address`) | App Functionality |
| Contact Info → **Other User Contact Info** | leads captured from the public profile (`leads`: name, email, phone) — collected about prospects by the coach | App Functionality |
| User Content → **Other User Content** | public coach profile, certifications, programs, recipes, exercise videos | App Functionality |

The coach app displays (but does not newly collect) client health data the client chose to share with their coach.

## Third parties that receive data (processors)

| Processor | Data | Purpose |
|---|---|---|
| Google Firebase (FCM, Crashlytics, Analytics, Remote Config, Performance) | device token, crash/usage diagnostics, pseudonymous user id | push, stability, product analytics (no ads) |
| Sentry | crash and performance diagnostics, pseudonymous user id | stability |
| Anthropic (default) / OpenAI (fallback) | minimum fields needed per AI feature (e.g. food photo, check-in answers), no names or emails; zero-retention / no-training settings | AI drafts reviewed by the coach |
| Payment gateways chosen by the coach (Stripe, Tap, PayTabs, HyperPay, MyFatoorah, Paymob, Flutterwave, PayPal) | name, email, amount | checkout |
| Meta WhatsApp Business (opt-in only) | phone number, reminder text | session and check-in reminders |
| Cloud hosting, object storage and CDN (EU / GCC regions) | all stored data, encrypted at rest | hosting |

## HealthKit / Health Connect statements (also required by Apple 2.5.1 / 5.1.3)

- Health data is used only to show the user and their coach their own progress and to auto-complete habits.
- It is never used for advertising, never sold, and never shared with third parties except the processors above for hosting.
- Each source can be disconnected; disconnecting with "delete stored data" removes the imported samples (`DELETE /me/integrations/{provider}?purge=1`).
