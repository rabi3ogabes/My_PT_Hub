# Categories, age ratings & content declarations

## Categories

| App | Store | Primary | Secondary | Rationale |
|---|---|---|---|---|
| Aurum (client) | App Store | **Health & Fitness** | Lifestyle | Workout logging, nutrition and habits are the core jobs; Health & Fitness is where "workout planner", "meal planner" and "تمارين" searches convert. |
| Aurum (client) | Google Play | **Health & Fitness** | — (tags: Workout tracker, Nutrition, Personal trainer) | Play allows one category plus up to five tags. |
| Aurum Coach | App Store | **Business** (test) ↔ **Health & Fitness** (control) | the other one | Coaches search "personal trainer app/software" in both. Run for 4 weeks each after launch; keep the category with higher impressions → installs for the 10 core keywords per locale (tracked in `docs/LAUNCH.md`). |
| Aurum Coach | Google Play | **Health & Fitness** | — (tags: Business tools, Scheduling, Fitness coaching) | Play category changes reset some ranking signals; test via custom store listings and Play experiments instead. |
| White-label apps | Both | **Health & Fitness** | Lifestyle | Same content as the client app. |

## Minimum age policy

- Aurum's terms require users to be **16 or older** (highest GDPR digital-consent age in our markets; KSA PDPL and Qatar PDPPL treat health data as sensitive).
- No content or features are directed at children; the AI guardrails flag users who state they are minors and route them to their coach (`tests/AiEvals` red-team fixtures).

## Apple age rating questionnaire (App Store Connect, 2025 questionnaire)

| Question | Client | Coach | Notes |
|---|---|---|---|
| Parental controls | No | No | |
| Age assurance | No | No | Age confirmed at sign-up (terms checkbox) |
| Unrestricted web access | No | No | In-app browser opens only checkout and help pages on allow-listed domains |
| User-generated content | **Yes** | **Yes** | Community posts, comments, chat. Moderation in place: report content, hide, mute member, coach-only announcement mode, block via report resolution (guideline 1.2) |
| Messaging and chat | **Yes** | **Yes** | Coach ↔ client chat, group chats |
| Advertising | No | No | |
| Medical or treatment information | **Infrequent / Mild** | **Infrequent / Mild** | General fitness and nutrition guidance, PAR-Q questionnaire; not diagnosis or treatment |
| Health or wellness topics | **Yes** | **Yes** | |
| Profanity / crude humor | None | None | Community moderated |
| Horror / fear, mature themes, violence (cartoon / realistic / graphic), sexual content, nudity | None | None | Progress photos are private (never shown publicly); transformation galleries require consent and are moderated |
| Alcohol, tobacco, drugs | None | None | Food database may include beverages; no promotion |
| Simulated gambling / contests / loot boxes | None | None | Challenges have no stakes or prizes in-app |
| **Calculated rating** | **13+** | **13+** | |
| **Rating we select** | **16+** (manual override up to match terms) | **16+** | Apple allows a higher rating than calculated |

## Google Play — content rating (IARC questionnaire)

| Section | Answer |
|---|---|
| Category | Reference, News, or Educational? **No** → "All other app types" |
| Violence, sexuality, language, controlled substance, crude humor, gambling | **No** to all |
| Users can interact or exchange content | **Yes** (chat, community) |
| Shares user-provided personal information with third parties | **No** (service providers only) |
| Shares precise physical location with other users | **No** |
| Allows purchase of digital goods | Client: **Yes** (coach programs where `sell_in_app_ios`/Play billing applies); Coach: **Yes** (SaaS plan) |
| Unrestricted internet access | **No** |
| **Expected result** | ESRB Everyone · PEGI 3 · USK 0 · ClassInd L — interactive elements: *Users Interact, In-App Purchases* |

## Google Play — target audience and content

| Item | Answer |
|---|---|
| Target age groups | **16–17, 18+** (not designed for children; Families policy not applicable) |
| Appeals to children? | No |
| Ads | **No ads** |
| News app | No |
| COVID-19 contact tracing / status | No |
| Government app | No |
| Financial features | No (payments are for coaching services; Aurum is not a financial service) |
| Health apps declaration | Fitness; Nutrition and weight management; Health and fitness coaching — see `privacy/data_safety.md` |
| Health Connect permissions | Read: Steps, ExerciseSession, HeartRate, RestingHeartRate, HeartRateVariabilityRmssd, SleepSession, Weight, BodyFat, Nutrition, ActiveCaloriesBurned. Write: ExerciseSession (completed workouts). Each with in-app rationale screen and privacy policy section. |
| Account deletion URL | `https://aurum.coach/en/legal/delete-account` |
| Login credentials for review | Demo client and demo coach accounts on production with seeded, flagged demo data; credentials stored in the release checklist vault, entered under App access |

## App Review notes (both stores)

```
Aurum is a coaching platform. Clients join their coach's private space with an invite code,
or use the app standalone with free self-guided plans.

Demo client:  review.client@aurum.coach  (password in App Review Information)
Demo coach:   review.coach@aurum.coach
Invite code for a fresh client sign-up: provided in App Review Information.

Payments: clients pay their coach for real-world personal training services (1:1 sessions,
coaching packages). Per guideline 3.1.3(e)/(d) these use the coach's payment provider.
Digital-only programs sold inside the iOS app use In-App Purchase or are hidden
(per-product setting, see docs/adr/0011-in-app-purchase-digital-content.md).

AI features produce drafts that the coach reviews before any client sees them. Health data
from HealthKit is used only for the user's and their coach's progress views.
```
