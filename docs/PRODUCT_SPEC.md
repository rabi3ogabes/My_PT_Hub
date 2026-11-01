# Aurum — Product Specification

> Status: **v1.0 (Phase 0 baseline)** · Owner: Product · Last updated: 2026-11-01
> Companion documents: [Information architecture](INFORMATION_ARCHITECTURE.md) · [User flows](USER_FLOWS.md) · [ERD](ERD.md) · [API contract](API_CONTRACT.md) / [OpenAPI](openapi.yaml) · [Design system](DESIGN_SYSTEM.md) · [Architecture](ARCHITECTURE.md)

## 1. Vision

**Aurum is the coaching platform professionals are proud to put their name on.** One calm,
fast, multilingual system where a personal trainer, online coach, nutritionist or studio runs
their whole business — programming, nutrition, accountability, communication, scheduling and
payments — and where their clients get an app so good they open it every day.

We win on five things competitors consistently get wrong:

1. **Speed** — the API answers in < 200 ms (p95); the logger never waits on the network.
2. **AI depth** — a copilot that drafts programs, meal plans, check-in replies and progressions, *included in Pro*, always approved by the coach.
3. **Language reach** — Arabic (true RTL), French, English and Spanish from day one, including regional food data (Gulf, Levant, Maghreb, France).
4. **Client-side growth** — every coach gets an SEO-indexed profile and a marketplace listing that brings them clients.
5. **Billing trust** — monthly by default, reminders before renewals, two-tap cancel, pro-rata refunds on annual.

### Positioning statement

> For **independent coaches and small studios** in the Gulf, the Maghreb and Europe who outgrow
> spreadsheets and WhatsApp, **Aurum** is the **premium coaching platform** that **runs their
> whole business in their clients' language**. Unlike My PT Hub, Trainerize, Everfit or
> PT Distinction, Aurum **includes AI in Pro, works offline, speaks Arabic natively, takes
> local payments and never traps coaches in their billing.**

Brand: **Aurum** (configurable via `BRAND_NAME`) · accent **champagne gold `#D4AF6A`** on near-black.
Launch markets in order: **Qatar → KSA → UAE → France → Morocco**.

## 2. Personas

| Persona | Snapshot | Jobs to be done | Pains today | What "great" looks like |
|---|---|---|---|---|
| **Sara — solo personal trainer** (Doha, 31) | 25 in-person clients + 10 online; trains at two gyms; Arabic + English | Program fast, track attendance and session packs, get paid, keep clients accountable between sessions | Session credits on paper, chasing cash, English-only apps confuse Arabic-speaking clients | Marks last week's sessions attended in one action; credits deduct; clients pay by Apple Pay / mada; app in Arabic |
| **Julien — online coach** (Lyon, 28) | 60 remote clients, Instagram-driven; French + English | Scale without losing the personal touch; weekly check-ins; content | Check-ins take his whole Sunday; tool costs grow per client; AI is an expensive add-on elsewhere | AI summarizes 60 check-ins and drafts replies in his voice; he approves in an hour |
| **Dr. Lina — nutritionist** (Riyadh, 36) | 120 clients, clinic + online | Build meal plans with regional food, track adherence, Ramadan plans | Food databases miss Gulf dishes; plans not followed; logging friction | Meal plan in Arabic with kabsa and harees within ±5 % of macros; clients log from plan in one tap |
| **Karim — studio owner** (Casablanca, 42) | 5 coaches, 300 members, classes + PT | Classes with capacity/waitlists, team management, commissions, branded app | Booking admin, no-show disputes, cash payments untracked, generic app | Studio plan: 5 coaches, branded app, commission report, cash recorded with receipt photo |
| **Omar — client** (Doha, 27) | Trains 4×/week with Sara; logs at the gym with poor signal | Know what to do today, log quickly, see progress, talk to coach | Logger loses data, pauses his music, no Arabic food | Opens "Today", taps ✓ through prefilled sets offline, PR celebration, syncs later |
| **Inès — standalone user** (Paris, 24) | No coach yet; wants a plan | Start free, build a habit, maybe hire a coach | Generic apps, no human path | Free guided plan, streaks; "find a coach" matches her with a French-speaking coach |

## 3. Competitor gaps → Aurum solutions

| # | Problem (observed in competitor reviews & interviews) | Who feels it | Aurum solution | Where it lives |
|---|---|---|---|---|
| 1 | **Slow apps**, spinners on every screen, history "buffering" | Clients, coaches | Offline-first Drift DB, cursor pagination, ETags, Octane-ready API with p95 < 200 ms budget enforced by k6 in CI | ARCHITECTURE §4, `infra/k6/api-smoke.js` |
| 2 | **Logging friction**: no prefill, sessions auto-pause and lose data, music stops | Clients | Last-performance prefill, "apply to remaining sets", never auto-pause, Drift persistence per tap, audio session mixes with music, lock-screen rest timer | Phase 2 logger, `GET /me/exercises/{id}/last` |
| 3 | **English-only** UIs and food data | Gulf/Maghreb/LatAm clients | ar/fr/en/es everywhere incl. RTL, translatable DB content, regional food packs, 4-language exercise search with synonyms | CONVENTIONS i18n, Phase 3 food DB |
| 4 | **Billing lock-in**: annual traps, hard cancellation, surprise renewals | Coaches | Monthly default, trial without card, reminders 7 d + 1 d before renewal, two-tap cancel, pro-rata annual refunds, "remove my card" | `POST /billing/cancel`, `saas_subscriptions.reminder_*` |
| 5 | **Weak nutrition**: foods added to plans don't appear, no recipes, thin plans | Nutritionists, clients | Plan → log in one tap (regression-tested), 150+ seeded recipes, Ramadan mode, swaps ±10 %, grocery lists, photo logging | Phase 3, `POST /me/food-logs/from-plan` |
| 6 | **No client discovery** | Coaches | SEO coach profiles, marketplace directory and matching quiz, reviews from verified clients only | Phase 8, `GET /public/coaches` |
| 7 | **AI as a paid add-on** or unsafe AI talking directly to clients | Coaches | AI copilot included in Pro, coach-approval gate, guardrails and evals | Phase 6, `ai_outputs.status` |
| 8 | **Stripe-only payments** | MENA/Africa coaches | Payment driver layer: Stripe Connect, Tap, PayTabs, HyperPay, MyFatoorah, Paymob, Flutterwave, PayPal, Manual | ADR 0007 |
| 9 | **Credits & calendar pain**: can't edit past sessions, calendar jumps back | PTs, studios | Retroactive bulk attendance, append-only credit ledger, calendar keeps selected week | Phase 4, `POST /bookings/bulk-status` |
| 10 | **Unreliable wearables sync** ("only when I open the app") | Clients | Webhooks + background delivery + scheduled pulls with dedupe by external id | Phase 7 |

## 4. Plans & pricing

Prices in USD; local currency shown on the pricing page (QAR, SAR, AED, EUR, MAD) at a rounded
psychological price; billing currency follows the coach's country. Annual = 10 × monthly
(2 months free). Source of truth: `backend/database/seeders/PlanSeeder.php` (`saas_plans`).

| | **Free** | **Pro** | **Studio** | **Enterprise** |
|---|---|---|---|---|
| Price | $0 | **$39 / month** ($390 / year) | **$99 / month** ($990 / year) | Custom (annual contract) |
| Trial | — | 14 days, no card | 14 days, no card | Pilot |
| Clients | **3** | Unlimited | Unlimited | Unlimited |
| Coaches (staff seats) | 1 | 1 | **5** (+$15 / extra seat) | Unlimited |
| Workouts, programs, nutrition, habits, check-ins, messaging, calendar | ✓ | ✓ | ✓ | ✓ |
| Client payments (any gateway, 0 % platform fee) | ✓ | ✓ | ✓ | ✓ |
| AI copilot | 20 requests / mo (taste) | ✓ 2,000 / mo | ✓ 8,000 / mo | Custom |
| Automations, community, challenges | — | ✓ | ✓ | ✓ |
| Integrations, public API, Zapier/Make, webhooks | — | ✓ | ✓ | ✓ |
| Coach profile on custom domain | — | ✓ | ✓ | ✓ |
| Video storage | 200 MB | 20 GB | 100 GB | Custom |
| Team roles, commissions, locations | — | — | ✓ | ✓ |
| **Branded client app** (published by Aurum) | — | — | ✓ | ✓ |
| Full white label (coach's own store account), SSO, DPA addendum, SLA 99.95 % | — | — | — | ✓ |

**Trust rules (non-negotiable):** monthly default; trial without card; email + push 7 days and 1 day
before trial end and before every renewal; cancel in two clicks/taps; cancellation = end of period
(monthly) or pro-rata refund (annual); card removable on request; invoices downloadable; dunning
emails on failed payment with 3 retries before downgrade to Free (data kept, nothing deleted).

## 5. Feature specification by module

Priority: **P0** = launch blocker · **P1** = within 90 days of launch · **P2** = roadmap.
Tier = lowest plan that includes the feature (F = Free, Pro, Studio, Ent).

### 5.1 Identity & accounts

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| ID-1 | As a coach, I sign up with email, Google or Apple so I can start in under a minute. | Register returns a session; tenant provisioned with defaults (currency/timezone from country); welcome email; `POST /auth/register`, `/auth/social/{provider}`. | P0 | F |
| ID-2 | As any user, I sign in with a magic link when I forget my password. | Link valid 15 min, single use (`magic_links.used_at`); response never reveals whether the email exists. | P0 | F |
| ID-3 | As an owner, I protect my account with TOTP 2FA. | Enrol (QR + recovery codes), confirm, challenge on login, disable with password; secrets encrypted; **owners and super-admins are forced to enrol** before accessing billing/payments. | P0 | F |
| ID-4 | As a mobile user, I stay signed in securely. | Access token 60 min + refresh 60 days rotated on every use; reuse of a refresh token revokes the family; tokens in secure storage. | P0 | F |
| ID-5 | As a user, I'm protected from brute force. | 10/min per IP and per account; account locks 15 min after 5 failures (423 `account_locked`); login activity logged. | P0 | F |
| ID-6 | As a user, I export or delete my data. | `POST /me/export` emails a zip (JSON + media) within 24 h; `DELETE /me` anonymises within 30 days; audit logged. | P0 | F |
| ID-7 | As a user, I choose language, units, theme and notification preferences. | Locale switch flips layout to RTL for Arabic instantly; units convert for display only; per-category push/email toggles + quiet hours. | P0 | F |
| ID-8 | As a user, I unlock the app with Face ID / fingerprint. | Optional biometric lock after 5 min in background; falls back to device passcode. | P1 | F |

### 5.2 Tenancy, team & roles

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| TN-1 | As a coach, my business is a tenant whose data never leaks. | Every tenant-owned row has `tenant_id` + global scope; cross-tenant isolation tests per module pass; route-model binding resolves after tenant. | P0 | F |
| TN-2 | As a coach, I complete onboarding (name, logo, brand colour, currency, language) in < 5 minutes. | Wizard with 5 steps; checklist on dashboard (`dashboard.onboarding.steps`); sample client + sample program preloaded and flagged. | P0 | F |
| TN-3 | As a coach, I invite clients by email, phone, link or QR, or import a CSV. | Invitation code (32 chars, expiring), QR SVG, resend/revoke; CSV import maps My PT Hub / Trainerize exports; Free plan blocks the 4th client with 402 `plan_limit_reached`. | P0 | F |
| TN-4 | As a studio owner, I invite coaches and assistants with granular permissions. | Roles owner/coach/assistant/client; assistants limited by permission set; seat limit by plan. | P0 | Studio |
| TN-5 | As a person in several businesses, I switch tenant. | `POST /tenants/switch`; `X-Tenant` header honoured; UI shows tenant brand. | P1 | F |

### 5.3 SaaS billing (coach → Aurum)

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| BL-1 | As a coach, I start a 14-day Pro trial without a card. | Trial on plan change; reminders at T-7 d and T-1 d (`reminder_7d_sent_at`, `reminder_1d_sent_at`); downgrade to Free at end if no card. | P0 | Pro |
| BL-2 | As a coach, I cancel in two clicks/taps. | Settings → Billing → Cancel → Confirm; monthly ends at period end; annual refunds pro-rata; confirmation email; `POST /billing/cancel`. | P0 | Pro |
| BL-3 | As a coach, I remove my card and download invoices. | `POST /billing/remove-card`; `saas_invoices` downloadable as PDF in the coach's language. | P0 | Pro |
| BL-4 | As Aurum, failed renewals are handled fairly. | Webhooks idempotent (`webhook_events`); 3 retries over 7 days with dunning emails; then downgrade to Free with all data retained. | P0 | Pro |
| BL-5 | As a coach in my currency, I see local prices. | Pricing page currency auto-detected; invoices in billing currency; VAT/TVA where applicable. | P1 | Pro |

### 5.4 Workouts — library, builder, programs, logger

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| WK-1 | As a coach, I search 2,000+ exercises in any of 4 languages. | Typo-tolerant search with synonyms ("DB bench" = "dumbbell bench press" = "ضغط صدر دمبل"); filters muscle/equipment/level; p95 < 100 ms. | P0 | F |
| WK-2 | As a coach, I add custom exercises with my own video from web or phone. | Chunked upload; FFmpeg HLS 240p–1080p + poster + 3 s preview; playable within 2 minutes; storage counted against plan. YouTube/Vimeo URLs embedded without download. | P0 | F |
| WK-3 | As a coach, I build workouts with any training format. | Block types straight, superset, giant, circuit, Tabata, EMOM, AMRAP, interval, warm-up, cool-down; set types warm-up/working/drop/failure/AMRAP; targets reps range, kg, %1RM, RPE/RIR, time, distance; autosave + undo. | P0 | F |
| WK-4 | As a coach, I build periodized programs and assign them. | Weeks × days grid, copy week, deload, progression rules; assign to clients/groups with start date and unlock mode (all/weekly drip/daily); pause/shift. | P0 | F |
| WK-5 | As a client, I log a workout fast, even offline, and never lose it. | Prefill from last performance; "apply to remaining sets"; rest timer on lock screen; music keeps playing; every tap persisted locally; session survives app kill; sync idempotent on client ULIDs. | P0 | F |
| WK-6 | As a client, my PRs are detected and celebrated. | e1RM (Epley), weight, reps and volume PRs on completion; celebration animation + coach notification. | P0 | F |
| WK-7 | As a coach, I see set-by-set history and charts per client. | Session detail, exercise history, volume per muscle per week, compliance planned vs done. | P0 | F |
| WK-8 | As a coach, I use 50+ starter templates (incl. Ramadan-friendly, calisthenics, HYROX). | Templates seeded; duplicate into my library; public templates browsable at `GET /public/program-templates`. | P1 | F |
| WK-9 | As a client, I swap an exercise for an approved alternative. | Alternatives defined by the coach or library; swap recorded on the set. | P1 | F |

### 5.5 Nutrition

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| NU-1 | As a coach, I calculate targets safely. | Mifflin-St Jeor / Katch-McArdle, TDEE by activity, macro split; floors 1,200 kcal (women) / 1,500 kcal (men) unless `override_safety`. | P0 | F |
| NU-2 | As a coach, I build meal plans from regional foods and recipes. | Fixed, macros-only, portions, IF and Ramadan plan types; live macro bars; swaps ±10 %; grocery list; PDF (RTL in Arabic). | P0 | F |
| NU-3 | As a client, I log a meal from my plan in one tap. | `POST /me/food-logs/from-plan`; planned items flagged logged; **regression test: a food added to a plan always appears**. | P0 | F |
| NU-4 | As a client, I log by search, barcode, recents, quick-add or copy day. | Barcode lookup falls back to Open Food Facts; full day loggable in < 60 s. | P0 | F |
| NU-5 | As a client, I log food from a photo. | AI candidates with confidence and grams; confirm sheet; low confidence asks for adjustment; logged < 10 s after confirm. | P1 | Pro* |
| NU-6 | As a client in Ramadan, my meals and water move around iftar and suhoor. | Fasting window by city; slots renamed; reminders shifted. | P0 | F |

\* Photo logging is a client-side AI tool the coach enables; usage counts against the coach's plan.

### 5.6 Habits, check-ins, progress & compliance

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| HB-1 | As a coach, I assign habits with schedules and reminders. | Boolean/count/duration/numeric; daily, specific days, X per week; reminders in client timezone; streaks robust to DST. | P0 | F |
| HB-2 | As a client, habits auto-complete from health data. | "10k steps", "8 h sleep" complete from HealthKit/Health Connect samples. | P1 | Pro |
| CK-1 | As a coach, I build check-in forms and schedule them. | Question types short/long text, scale, single/multi choice, number, photo, measurement, file; weekly / every N days / program week end. | P0 | F |
| CK-2 | As a client, I submit a check-in with photos and measurements step by step. | Pose ghost overlay; unit switch; reminder push + email; overdue escalation to coach. | P0 | F |
| CK-3 | As a coach, I review a check-in with context and reply in the same thread. | Answers + trends + photo compare + comments; mark reviewed. | P0 | F |
| PR-1 | As a client, I see weight, measurements, photos and PRs over time. | Charts, compare slider, monthly recap; photos private, encrypted, signed URLs (10 min). | P0 | F |
| CM-1 | As a coach, I see who is at risk today. | Score from workouts/nutrition/habits/check-ins; red = 3+ days inactive or > 25 % drop; daily digest; bulk message. | P0 | F |

### 5.7 Messaging, notifications, community

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| MS-1 | As coach and client, we chat in real time. | Text, voice notes (AAC + waveform), images, video, files, share cards; replies, reactions, edit ≤ 15 min, delete for everyone; read receipts, typing; 5,000-message history scrolls smoothly; encrypted at rest. | P0 | F |
| MS-2 | As a coach, I broadcast to segments and schedule messages. | Segments by tag, group, program, risk; preview recipients; scheduled send. | P0 | F |
| MS-3 | As a coach, I automate follow-ups. | Triggers (joined, program start/finish, missed N days, check-in overdue, PR, birthday, package expiring, payment failed) → conditions → actions; each fires once per dedupe key and is logged. | P1 | Pro |
| NT-1 | As a user, notifications are reliable and respectful. | Push (FCM/APNs), email, in-app; per-category prefs; quiet hours; invalid tokens cleaned; deep links. | P0 | F |
| CO-1 | As a coach, I run a community with spaces, posts and moderation. | Posts (text/media/poll/workout share), comments, reactions, pin, hide, report, announcement-only spaces. | P1 | Pro |
| CO-2 | As a coach, I run challenges with leaderboards and badges. | Metrics steps/workouts/habit streak/volume/custom; teams optional; badges awarded. | P1 | Pro |

### 5.8 Calendar & bookings

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| CA-1 | As a coach, I publish availability and clients book 1:1 slots. | Weekly rules, overrides, buffers, min notice, max advance; slots respect timezones/DST (Doha/Paris/Casablanca tests); double booking prevented by DB constraint + lock. | P0 | F |
| CA-2 | As a coach, I run classes with capacity and waitlists. | Waitlist auto-promotion with claim window; QR check-in; RRULE series editing (this/following/all). | P0 | F |
| CA-3 | As a coach, I mark attendance retroactively in bulk and credits stay correct. | `POST /bookings/bulk-status`; append-only `credit_ledger`; balance always equals sum of entries; calendar stays on the selected week. | P0 | F |
| CA-4 | As a client, I get reminders and can add sessions to my calendar. | 24 h + 2 h push/email (WhatsApp in Phase 7); ICS download. | P0 | F |

### 5.9 Client payments & business analytics

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| PY-1 | As a coach in Qatar, I sell a package via a local gateway with Apple Pay. | Tap driver; content auto-assigned within 10 s of payment via `FulfilOrder`. | P0 | F |
| PY-2 | As a coach, I record cash or bank transfer with a receipt photo. | Manual payment triggers the same fulfilment; idempotency key required. | P0 | F |
| PY-3 | As a coach, I issue compliant invoices. | Sequential per tenant (`invoice_sequences`), VAT: Qatar 0 %, KSA 15 % (ZATCA fields), UAE 5 %, France 20 %; credit notes for refunds; Arabic RTL PDF. | P0 | F |
| PY-4 | As a client, I cancel a subscription in two taps. | `POST /me/subscriptions/{id}/cancel`; access to period end. | P0 | F |
| PY-5 | As a coach, iOS purchases follow Apple rules per product. | `sell_in_app_ios`: hidden / external / IAP; physical services use external payment (ADR 0011). | P0 | F |
| AN-1 | As a coach, I see MRR, churn, LTV and cohorts. | Daily snapshots; dashboard MRR equals sum of active subscriptions (test). | P1 | Pro |
| AN-2 | As a studio owner, I see revenue and retention per coach and commissions. | Commission rules (% package / per session); CSV payout export. | P1 | Studio |

### 5.10 CRM, contracts & marketplace

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| CR-1 | As a coach, I manage leads in a pipeline. | Custom stages; activities (note/call/email/task); convert lead → client in one click with invite + checkout link. | P1 | F |
| CR-2 | As a coach, clients sign contracts, waivers and PAR-Q digitally. | Draw/type signature; PDF with IP, timestamp and SHA-256 hash; PAR-Q flags surface in AI guardrails. | P1 | F |
| MK-1 | As a coach, I get a public SEO profile with booking and checkout. | `/{locale}/coaches/{slug}`; LocalBusiness/Person schema; packages; verified reviews only. | P1 | Pro |
| MK-2 | As a standalone client, I find a coach through a quiz. | `POST /marketplace/match` returns ranked coaches; introduction request creates a lead. | P1 | F |

### 5.11 AI copilot

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| AI-1 | As a coach, I generate a program from a sentence. | "4-day hypertrophy, dumbbells only, 45 min" → editable draft in < 20 s using library exercises only. | P1 | Pro |
| AI-2 | As a coach, I generate a meal plan within ±5 % of targets. | Server-side macro verification; regenerate if out of range; Arabic output with Gulf dishes. | P1 | Pro |
| AI-3 | As a coach, I get check-in summaries and drafted replies in my tone. | Wins/struggles/trends with numbers, risk flags; nothing sent without approval (`ai_outputs.status`). | P1 | Pro |
| AI-4 | As a coach, I get weekly progression suggestions as a diff. | Load/volume/swap per exercise; apply with one click. | P2 | Pro |
| AI-5 | As Aurum, AI is safe and metered. | Refuse/flag ED, injury diagnosis, minors, extreme deficits; per-tenant usage and cost (`usage_meters`, `ai_requests.cost_micros`); eval suite nightly. | P1 | Pro |
| AI-6 | As a coach, I ask questions about my business in plain language. | Tenant-scoped tool functions only, no model-written SQL. | P2 | Pro |
| AI-7 | As a client, I get form feedback on a set video (coach reviewed). | ≤ 30 s upload; annotated frames + draft; not-medical-advice disclaimer. | P2 | Pro |

### 5.12 Integrations & wearables

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| IN-1 | As a client, Apple Health / Health Connect data arrives without opening the app. | Background delivery; samples deduped by `external_id`; coach sees steps within 15 min. | P1 | F |
| IN-2 | As a client, Garmin, Fitbit, Oura, Whoop, Strava, Withings, Polar connect reliably. | Webhooks where available; no duplicate workouts across sources (priority rules). | P2 | Pro |
| IN-3 | As a coach, bookings sync two-way with Google/Outlook. | External busy blocks reduce availability; platform bookings appear externally. | P1 | Pro |
| IN-4 | As a coach, reminders go out on WhatsApp in the client's language. | WhatsApp Cloud API templates in 4 languages; opt-in tracked; replies land in inbox. | P1 | Pro |
| IN-5 | As a developer coach, I use the API, webhooks and Zapier/Make. | Scoped tokens; signed webhooks with retries and delivery log; Zapier triggers fire < 1 min. | P2 | Pro |

### 5.13 Marketing site, white label, super-admin

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| WEB-1 | As a visitor, the marketing site is fast and in my language. | `/{locale}/…` with hreflang; Lighthouse mobile ≥ 95 perf, SEO 100, a11y ≥ 95 (CI); LCP < 2.0 s. | P1 | — |
| WEB-2 | As Aurum, programmatic SEO pages bring traffic. | 2,000+ exercise pages × 4 locales, calculators, templates, recipes in sitemaps; schema validated. | P1 | — |
| WL-1 | As a Studio owner, my clients get an app with my brand. | Container model by default (branding after invite code); separate binary via `build_whitelabel tenant:<slug>` → TestFlight in one pipeline run (ADR 0010). | P1 | Studio |
| SA-1 | As super-admin, I manage tenants, plans, flags, translations and master data. | Filament panel; impersonation with banner + audit log; system health (queues, failed jobs, Reverb, storage). | P0 | — |

### 5.14 Offline sync & localization (cross-cutting)

| ID | User story | Acceptance criteria | Pri | Tier |
|---|---|---|---|---|
| SY-1 | As a client, every write works offline and syncs later. | Local first → sync queue with retry/backoff; `POST /sync/push` returns applied/duplicate/rejected per mutation; LWW per field; session sets append-only. | P0 | F |
| L10N-1 | As an Arabic user, the whole product is natively RTL. | Logical CSS, `EdgeInsetsDirectional`; Arabic font stack; numbers/dates/currency via intl; golden tests LTR + RTL. | P0 | F |

## 6. Non-functional requirements

| Area | Requirement | How we verify |
|---|---|---|
| Performance — API | p95 < 200 ms, p99 < 500 ms at 10k concurrent clients; message send p95 < 150 ms | k6 `infra/k6/api-smoke.js` (smoke in CI, load before release), APM |
| Performance — web | LCP < 2.0 s, CLS < 0.05, INP < 200 ms (mobile) | Lighthouse CI budgets (`infra/lighthouse/lighthouserc.json`) |
| Performance — apps | cold start < 2 s mid-range Android; logger 40 sets at 60 fps; exercise search < 100 ms | Firebase Performance, integration tests |
| Availability | 99.9 % monthly (Enterprise SLA 99.95 %); RPO 15 min, RTO 1 h | Uptime monitor, status page, quarterly restore drill |
| Reliability | crash-free sessions > 99.8 %; zero data loss for offline logs | Crashlytics/Sentry gates on staged rollout |
| Security | OWASP ASVS L2; encrypted health data; signed media URLs; audit log; 2FA for owners/admins; pentest before launch | [SECURITY.md](SECURITY.md) checklist |
| Privacy | GDPR, Qatar PDPPL, KSA PDPL; export/delete; DPA; subprocessors list; data residency options for Enterprise | SECURITY.md §4 |
| Accessibility | WCAG 2.2 AA, 44 pt touch targets, VoiceOver/TalkBack in English and Arabic | axe in CI, manual audits |
| Localization | ar (RTL), fr, en, es complete at launch; no hard-coded strings | lint for raw strings, translation coverage report |
| Scalability | stateless app containers; Redis queues with Horizon autoscaling; read replicas when > 60 % CPU | capacity reviews |
| Maintainability | backend domain coverage > 80 %; Larastan level 8; Pint; flutter analyze clean | CI (`.github/workflows`) |
| Observability | structured JSON logs with request id; Sentry; queue backlog alerts; webhook failure alerts | runbooks in `docs/runbooks` |

## 7. KPIs

| KPI | Definition | Target (6 months post-launch) | Source |
|---|---|---|---|
| **Coach activation** | % of new coach tenants that create a first program **and** invite a client within 24 h of sign-up | ≥ 45 % | `programs.created_at`, `invitations.created_at` vs `tenants.created_at` |
| Time to first program | median minutes from sign-up to first program assigned | < 5 min | event analytics |
| **Client weekly active rate** | % of active clients with ≥ 1 logged workout, food log, habit log or check-in in the trailing 7 days | ≥ 65 % | `analytics_daily`, logs |
| Client time to first workout | median from invite acceptance to first logged set | < 2 min | event analytics |
| **Coach churn** | % of paying coach tenants cancelling per month (logo churn) | < 3 % monthly | `saas_subscriptions` |
| Trial → paid conversion | % of trials converting within 30 days | ≥ 25 % | billing |
| Net revenue retention | MRR from a cohort after 12 months / initial | ≥ 105 % | billing |
| **NPS** | quarterly in-app survey (coaches and clients separately) | Coaches ≥ 50, clients ≥ 45 | in-app survey |
| AI acceptance rate | approved or edited / generated drafts per feature | ≥ 70 % | `ai_outputs.status` |
| Store rating | App Store + Play average | ≥ 4.7 | store consoles |
| Crash-free sessions | per release | > 99.8 % | Crashlytics / Sentry |
| Marketplace leads per published coach | leads / month | ≥ 3 | `leads.source = profile|marketplace` |

## 8. Out of scope for v1

Native watchOS/Wear OS apps beyond Phase 7b, desktop apps, physical goods shipping/logistics,
medical/clinical features (diagnosis, prescriptions), payroll, and marketplace payments where
Aurum is merchant of record (coaches are merchant of record through their own gateways).

## 9. Open questions

1. Seat price for Studio beyond 5 coaches ($15 proposed) — validate in closed beta.
2. Marketplace monetization after launch (featured listings vs referral fee) — default free.
3. Data residency for KSA Enterprise customers (in-Kingdom hosting) — evaluate demand by T+90 d.
