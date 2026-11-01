# Information Architecture

Four surfaces share one domain model: the **marketing site** (public, SEO), the **coach
dashboard** (web + coach app, full parity), the **client portal / app** and the **super-admin**
panel. Navigation labels are translation keys; the English labels below are the source strings.

## 0. Domains & URL conventions

| Surface | Host | Notes |
|---|---|---|
| Marketing + coach profiles | `aurum.coach/{locale}/…` | `ar`, `fr`, `en`, `es`; `x-default` → `en`; localized slugs; hreflang on every page |
| Coach dashboard + client web portal | `app.aurum.coach` and `{tenant}.app.aurum.coach` | Tenant resolved from subdomain, `X-Tenant` header or the user's current tenant |
| API | `api.aurum.coach/api/v1` | See [openapi.yaml](openapi.yaml) |
| Realtime | `ws.aurum.coach` (also `/app` on the web origin) | Reverb (Pusher protocol) |
| Super-admin | `app.aurum.coach/admin` | Filament, super-admins only, 2FA enforced |
| Deep links | `https://app.aurum.coach/invite/{code}`, `aurum://…` | Universal links / App Links (`/.well-known/*`) |
| White-label | `{brand}.aurum.coach` or coach's domain | Profile, privacy policy and deep-link domain per tenant |

## 1. Marketing site

```
/{locale}                                    Home — hero video, social proof, why switch, feature demos, pricing teaser, CTA
├── /features                                Features hub
│   ├── /features/workouts                   Builder, library, logger
│   ├── /features/nutrition                  Meal plans, regional foods, Ramadan mode, photo logging
│   ├── /features/ai-copilot                 AI included in Pro, coach approval
│   ├── /features/check-ins                  Forms, review, compliance
│   ├── /features/payments                   Gateways, packages, invoices
│   ├── /features/bookings                   Calendar, classes, credits
│   ├── /features/community                  Spaces, challenges, badges
│   ├── /features/white-label                Branded app (Studio)
│   └── /features/integrations               Wearables, calendars, Zoom, WhatsApp, Zapier
├── /pricing                                 Monthly/annual toggle, currency auto-detect, comparison table, "cancel in 2 clicks", FAQ
├── /ai                                      AI copilot story (alias of /features/ai-copilot with long-form content)
├── /for-personal-trainers                   Solution page
├── /for-online-coaches
├── /for-nutritionists
├── /for-gyms                                Gyms & studios (Studio plan)
├── /for-physiotherapists
├── /for-crossfit-hyrox
├── /for-womens-coaches
├── /coaches                                 Coach directory (filters: city, specialty, language, online/in-person, price, coach gender, rating)
│   ├── /coaches/{city}                      City landing pages (doha, riyadh, jeddah, dubai, abu-dhabi, paris, lyon, casablanca, madrid…) — LocalBusiness schema
│   └── /coaches/{slug}                      Coach profile: intro video, specialties, certifications, packages + checkout, booking, reviews, FAQ
├── /exercises                               Exercise library (2,000+ × 4 locales)
│   ├── /exercises/muscle/{muscle}
│   ├── /exercises/equipment/{equipment}
│   └── /exercises/{slug}                    Video, muscles diagram, HowTo steps, mistakes, alternatives, related programs
├── /templates                               Free templates (gated download → nurture)
│   ├── /templates/workout-programs/{slug}
│   ├── /templates/meal-plans/{slug}         incl. Ramadan, Gulf and Maghreb plans
│   ├── /templates/check-in-forms/{slug}
│   └── /templates/par-q
├── /calculators
│   ├── /calculators/tdee   /bmr   /macros   /one-rep-max   /body-fat   /bmi
│   ├── /calculators/protein   /water   /pace   /calories-burned
│   ├── /calculators/pt-pricing
│   └── /calculators/online-coach-income
├── /recipes   /recipes/{slug}               Recipe schema with nutrition facts
├── /blog                                    Academy: business, programming, nutrition, local guides
│   ├── /blog/category/{category}
│   └── /blog/{slug}
├── /compare                                 Factual comparison hub
│   ├── /compare/my-pt-hub   /trainerize   /everfit   /pt-distinction   /truecoach
├── /switch                                  Migration landing — "Switch in 1 day" (CSV import + done-for-you)
├── /help                                    Help centre (articles by module, search, contact)
├── /about   /careers   /press   /contact   /roadmap   /changelog   → status.aurum.coach
├── /legal
│   ├── /legal/terms   /privacy   /dpa   /cookies   /refunds   /subprocessors   /acceptable-use
│   └── /legal/delete-account                Web deletion request (store requirement)
├── /login   /register   /forgot-password    (app host)
└── /sitemap.xml  →  sitemap index per locale & type (pages, exercises, templates, coaches, recipes, blog)
```

Localized slug examples: `/fr/logiciel-coach-sportif` (= /for-personal-trainers),
`/ar/برنامج-المدرب-الشخصي`, `/es/software-entrenador-personal`. The full slug map lives in
[`seo/keyword-map.md`](seo/keyword-map.md).

**Global marketing navigation:** Features ▾ · Solutions ▾ · Pricing · Coaches · Resources ▾ (Exercises, Templates, Calculators, Recipes, Blog, Help) · Language switcher · Log in · **Start free**.

## 2. Coach dashboard (web) and coach app

Sidebar order = coach app "More" order. Items unavailable on the current plan show an upgrade
sheet rather than disappearing (discoverability without dead ends).

```
Dashboard                    KPIs (active clients, at risk, check-ins to review, unread, sessions today, MRR, revenue), at-risk list, today, activity, onboarding checklist
Clients                      List (filters: status, risk, coach, tag, group; search; sort) · Invite · Import CSV · Groups · Invitations
└── Client profile           Overview (schedule strip, compliance, last check-in, payments) · Workouts · Nutrition · Habits · Check-ins · Progress · Health data · Messages · Bookings & credits · Payments · Contracts · Notes & tags · Settings
Programs                     Library · Builder (weeks × days grid) · Templates · Assignments
Workouts                     Library · Builder (library | canvas | inspector) · Import/Export
Exercises                    Library (grid/list, filters, favourites) · Custom exercise + video upload
Nutrition                    Meal plans · Plan builder · Recipes · Foods (custom) · Targets calculator
Habits                       Library · Assignments
Check-ins                    Inbox (unread / overdue / reviewed) · Forms · Schedules · Review screen
Calendar                     Day / week / month / resource views · Availability · Locations · Attendance register · Booking settings
Messages                     Inbox (conversations | thread | client context) · Broadcasts · Scheduled · Saved replies · Automations
Community                    Spaces · Moderation queue · Challenges · Badges
Payments                     Orders · Subscriptions · Invoices · Manual payment · Refunds
Packages                     Products & prices · Coupons · Payment links & QR · Checkout preview
Analytics                    Business (MRR, revenue, churn, LTV, cohorts) · Coaches (Studio) · Exports
CRM / Leads                  Pipeline (kanban) · Lead detail · Forms · Contracts & waivers · Signed documents
Marketing                    Coach profile · Reviews · Referral program · Lead capture embeds
AI Copilot                   Drafts to review · Settings (tone, samples, features) · Usage
Team                         Members · Roles & permissions · Commissions (Studio)
Settings                     Business · Branding · Languages · Payments (gateways, tax, invoice branding) · Integrations · Developer (API tokens, webhooks) · Notifications · Privacy · White-label app (Studio)
Billing                      Plan · Usage · Card · Invoices · Cancel (two clicks)
```

Global elements: **✦ Copilot command bar** (Cmd/Ctrl+K: search clients, programs, exercises;
"Create a program…", "Summarize Sara's last month"), notification centre, tenant switcher,
language and theme toggles, help.

**Coach app bottom navigation (5):** Home (dashboard) · Clients · Inbox · Calendar · More
(everything else in the sidebar order above). Quick actions FAB: invite client, record payment,
create workout, new broadcast.

## 3. Client portal (web) and client app

**Bottom navigation (5):** Today · Train · Nutrition · Progress · Messages. Calendar, Community,
Shop, Habits and Profile are reachable from Today cards and the avatar menu.

```
Today                        Greeting, today's workout card (Start), streak, next session, nutrition rings, habits checklist, check-in due, unread messages, AI weekly recap (approved)
Train (Workouts)             Current program (weeks, unlocked days) · Workout preview (download for offline) · Logger · History · Exercise detail & history · PR board
Nutrition                    Day view (rings, meals, water, fasting timer) · Quick log sheet (search, barcode, recents, favourites, from plan, quick add, photo) · Meal plan · Recipes & cook mode · Week summary
Habits                       Today's checklist · Streaks · Reminders
Progress                     Weight & measurements · Photos & compare slider · PRs · Compliance · Monthly recap · Check-ins (list, step-by-step form, coach thread)
Messages                     Conversations · Thread (voice notes, media, share cards, reactions)
Calendar                     Upcoming bookings · Book 1:1 · Classes · Waitlist offers · Credits · QR check-in
Community                    Spaces feed · Post composer · Challenges & leaderboards · Badges
Shop                         Coach's products · Checkout · Purchases · Subscriptions (cancel in 2 taps) · Invoices · Contracts to sign
Profile                      Account · Language · Units · Theme · Notifications · Connected devices (Health, wearables) · Privacy (export / delete) · Biometric lock · Help · About
Standalone mode              Goal quiz · Free plan · AI coach chat (guard-railed) · Find a coach (directory, match quiz)
```

## 4. Super-admin (Filament, `/admin`)

```
Overview                     Platform KPIs (tenants, MRR, trials, churn), system health tiles
Tenants                      List · Detail (plan, usage, members, flags) · Impersonate (banner + audit log entry) · Suspend
Users                        Search · Lock/unlock · Force 2FA reset · GDPR export/delete requests
Plans                        SaaS plans (prices, limits, features) · Coupons
Subscriptions                SaaS subscriptions · Invoices · Failed payments / dunning · Webhook events (replay)
Master data                  Exercises (curation, translations, media) · Foods (moderation of user submissions, regional packs) · Recipes · Program templates
Translations                 Editor for DB translations (cache-busted) · Coverage report per locale
Feature flags                Key · enabled (kill switch) · plans · tenant allow-list · rollout %
AI                           Models per feature · Prompt versions · Usage & cost per tenant · Eval results
White-label apps             Apps per tenant · Build status · Store review status · Version
Marketplace                  Coach verification (certificates) · Featured listings · Reviews moderation
Content                      Blog / CMS (SEO fields, schedule, translations)
Support                      Impersonation log · Activity log search · Announcements
System health                Queues (Horizon) · Failed jobs · Reverb connections · Storage · Scheduler heartbeats · Backups (last success, restore drill)
```

## 5. Cross-surface objects

| Object | Coach surfaces | Client surfaces | Public surfaces |
|---|---|---|---|
| Program / workout | Builder, assignments | Train, logger | Templates |
| Meal plan / recipe | Plan builder | Nutrition, cook mode | Templates, recipes |
| Check-in | Inbox, review | Step-by-step form, thread | Check-in form templates |
| Product / package | Packages, payments | Shop, purchases | Coach profile checkout, payment links |
| Booking / event | Calendar | Calendar | Coach profile booking |
| Coach profile | Marketing → profile | Find a coach | `/coaches/{slug}` |
| Lead | CRM pipeline | — | Enquiry form, match quiz |
