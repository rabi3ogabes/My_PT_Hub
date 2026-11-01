# Apple Custom Product Pages & Google Play custom store listings

Custom pages let each ad audience land on screenshots and copy that speak to them. Every page
reuses the main listing's metadata limits (validated by `store/validate.mjs` once added as
`store/<app>/cpp/<id>/<locale>/`). Deep link each page from its Apple Search Ads ad group or
campaign URL (`?ppid=<page id>`).

## Coach app — App Store Custom Product Pages

| ID | Audience | Promotional text (≤170) | Screenshot order (frames from `store/coach/<locale>/screenshots.md`) | Ad groups / keywords |
|---|---|---|---|---|
| `cpp-trainers` | Solo personal trainers (in-person + hybrid) | "Programs, bookings and payments in one app. Mark a week of sessions in one tap and get paid automatically." | 1 → 7 → 6 → 3 → 4 → 5 → 2 → 8 | personal trainer app, pt software, client management, booking |
| `cpp-nutritionists` | Nutritionists & dietitians | "Meal plans from regional foods with macros within 5% of targets, Ramadan plans, grocery lists and check-ins your clients actually fill in." | custom: nutrition plan builder → AI meal plan draft → check-in review → client food log → compliance → payments → calendar → languages | nutritionist app, meal plan software, diet coaching |
| `cpp-gyms` | Gyms, studios, boutique owners (Studio plan) | "Five coaches, one brand. Classes with waitlists, credits, team commissions and your own branded client app." | custom: studio dashboard with coach filter → class calendar → branded app preview → commissions report → bookings → payments → community → languages | gym management app, studio software, class booking |
| `cpp-arabic` | Arabic-speaking coaches (QA, SA, AE, KW) — Arabic locale only | "أول منصة تدريب عربية بالكامل: برامج وتغذية خليجية ومتابعات ومدفوعات محلية مع مساعد ذكاء اصطناعي يكتب بأسلوبك." | 8 (Arabic RTL hero) → 2 → 1 → 5 → 6 → 3 → 4 → 7 | برنامج مدرب شخصي, تطبيق مدرب رياضي, إدارة متدربين |

## Client app — App Store Custom Product Pages

| ID | Audience | Promotional text | Screenshot order | Use |
|---|---|---|---|---|
| `cpp-ramadan` | Gulf / Maghreb users before and during Ramadan | "Ramadan mode moves your meals and water around iftar and suhoor, with lighter training plans that fit the month." | nutrition with fasting timer → Ramadan plan → workout logger → habits (water) → coach chat → progress → offline → languages | Apple Search Ads Ramadan campaign, In-App Event "Ramadan Strong" |
| `cpp-coach-invited` | Clients invited by a coach (QR / link) | "Your coach is waiting. Sign in with your invite code to see your plan." | coach chat → program (reviewed by coach) → logger → nutrition → check-in → bookings → progress → languages | Invite emails and QR (smart app banner `app-argument`) |
| `cpp-self-guided` | Standalone users from consumer search | "Start free with a guided plan. Log workouts in seconds and find a certified coach when you are ready." | 1 → 4 → 7 → 6 → 2 → 5 → 3 → 8 | workout planner, gym log, calorie counter |

## In-App Events (App Store) & Play promotional content

| Event | Window | Badge | Short description (≤50) | Deep link |
|---|---|---|---|---|
| Ramadan Strong challenge | 7 days before Ramadan → Eid | Challenge | Train smart and stay hydrated this Ramadan | `aurum://challenges/ramadan-strong` |
| New Year Reset | Jan 1–31 | Challenge | 30 days of workouts, one streak | `aurum://challenges/new-year-reset` |
| Summer Shred (Gulf) | Jun 1–30 | Competition | Join your coach's summer challenge | `aurum://challenges/summer` |
| Launch week live Q&A (coach app) | Launch week | Live Event | Build your first program with us live | `aurum://live/launch` |

## Google Play custom store listings (per country)

| Listing | Countries | Default language | What changes |
|---|---|---|---|
| Gulf | QA, SA, AE, KW, BH, OM | ar | Arabic title/short description first, Gulf food screenshots (كبسة, مجبوس), local gateways (mada, Apple Pay, KNET) mentioned in full description, Ramadan feature graphic during Ramadan |
| Maghreb | MA, DZ, TN | fr (+ ar) | French copy with Maghreb dishes (couscous, harira, msemen), cash/manual payments emphasised for coaches |
| France & Belgium | FR, BE, LU, CH | fr | TVA invoices for coaches, French dishes, "résiliation en deux gestes" promise |
| Spain & LatAm | ES, MX, AR, CO, CL | es | Spanish copy (es-ES for Spain, es-419 for LatAm), regional dishes |
| Default | all others | en | main listing |

Play experiments: run one variable at a time (icon, then first screenshot, then short description) per listing, 50/50, minimum 7 days and 90% confidence before applying.
