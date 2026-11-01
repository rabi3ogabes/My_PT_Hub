# Aurum Design System

**Professional, luxury, modern, smart.** Near-black and warm off-white surfaces, one signature
accent — champagne gold `#D4AF6A` — large confident type, quiet motion, and nothing that
makes a coach or client wait.

Source of truth: [`packages/design_tokens/tokens.json`](../packages/design_tokens/tokens.json).
`node packages/design_tokens/generate.mjs` (or `make tokens`) writes:

| Output | Consumer |
|---|---|
| `backend/resources/css/tokens.css` | Tailwind v4 `@theme` + light/dark CSS variables (`--au-*`), imported by `resources/css/app.css` |
| `packages/core/lib/src/design/app_tokens.dart` | Flutter constants (`AuBrandColors`, `AuDarkColors`, `AuLightColors`, `AuSemanticColors`, `AuMacroColors`, `AuSpacing`, `AuRadius`, `AuMotion`, `AuType`, `kAuTouchTarget`) consumed by `AuTheme` / `AuThemeData` |

Never hard-code a colour, size or duration in Blade, Livewire or Flutter — use a token.

## 1. Tokens

### Colour

| Token | Dark | Light | Use |
|---|---|---|---|
| `background` | `#0B0B0F` | `#FAF8F4` | App/page background |
| `surface` | `#16161D` | `#FFFFFF` | Cards, sheets |
| `surfaceRaised` | `#1E1E27` | `#FFFFFF` | Popovers, raised cards |
| `surfaceSunken` | `#101015` | `#F2EFE8` | Inputs, wells, table stripes |
| `border` / `borderStrong` | `#2A2A35` / `#3A3A48` | `#E7E2D8` / `#D2CBBD` | Hairlines / focused or selected outlines |
| `textPrimary` | `#F5F3EE` | `#121217` | Body and headings |
| `textSecondary` | `#B4B1AA` | `#4B4A52` | Supporting text |
| `textMuted` | `#7D7A74` | `#86848C` | Captions, placeholders (never for essential info) |
| `overlay` | `#000000B3` | `#0B0B0F80` | Scrims behind modals and sheets |

Brand: `gold #D4AF6A` (accent, primary buttons, focus ring, active nav), `goldLight #E8CF9B`
(hover, charts), `goldDark #A9853F` (pressed, gold text on light backgrounds for contrast),
`onGold #0B0B0F` (text on gold).

Semantic: `success #3DBE8B`, `warning #F2B544`, `danger #EF5A5A`, `info #5B8DEF`, each with a
`*Soft` 12 % alpha variant for backgrounds. Risk status uses green/amber/red = success/warning/danger.

Macros: protein `#E76F6F`, carbs `#D4AF6A`, fat `#6FA8E7`, kcal `#3DBE8B` — identical on web,
apps, PDFs and charts so clients learn them once.

**Contrast (WCAG 2.2 AA):** text on `background`/`surface` ≥ 4.5:1 in both themes; gold is used
for text only at ≥ 18 px bold on dark (≈ 8.9:1) or as `goldDark` on light (≈ 3.4:1 → large text
only; body links on light use `textPrimary` + underline). `onGold` on gold ≈ 9.1:1.

### Typography

Latin **Manrope**, Arabic **IBM Plex Sans Arabic**, numbers in tables/timers use tabular figures
(`font-feature-settings: "tnum"`), mono **JetBrains Mono** for codes (invite codes, API tokens).
Fonts are bundled in `packages/core/assets/fonts` (apps) and served via Bunny Fonts with
`font-display: swap` and an Arabic subset (web).

| Style | Size / line height | Weight | Tracking | Use |
|---|---|---|---|---|
| `display` | 48 / 1.05 | 800 | −0.03 em | Marketing hero, PR celebration number |
| `h1` | 36 / 1.10 | 800 | −0.025 em | Page titles |
| `h2` | 28 / 1.15 | 700 | −0.02 em | Section titles, KPI values |
| `h3` | 22 / 1.20 | 700 | −0.015 em | Card titles |
| `title` | 18 / 1.30 | 600 | −0.01 em | List titles, sheet titles |
| `body` | 16 / 1.50 | 400 | 0 | Default text |
| `bodySmall` | 14 / 1.45 | 400 | 0 | Dense tables, secondary text |
| `label` | 13 / 1.30 | 600 | 0.01 em | Buttons, form labels |
| `caption` | 12 / 1.30 | 500 | 0.02 em | Meta, timestamps |
| `overline` | 11 / 1.20 | 700 | 0.12 em | Section eyebrows (uppercase in Latin only) |

Arabic rules: no negative tracking and no uppercase transforms; line height +0.1 for
`body`/`bodySmall`; Arabic display sizes step down one level (Arabic glyphs are taller).

### Spacing, radius, elevation, motion

- **Spacing (4 pt grid):** `0, 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80` (`s0…s20`). Screen gutters 16 (phone) / 24 (tablet) / 32 (desktop); card padding 16–24; list row height ≥ 56.
- **Radius:** `xs 6` chips · `sm 10` inputs · `md 14` buttons, cards · `lg 20` sheets, modals · `xl 28` hero cards · `pill 999`.
- **Elevation:** `e1` resting cards (light theme only), `e2` popovers, `e3` modals/sheets, `glow` gold focus/active state. Dark theme separates layers with surface tone rather than shadow.
- **Motion:** `fast 150 ms` (press, toggle), `base 220 ms` (sheet, tab, card expand), `slow 300 ms` (page, celebration entrance); curves `easeOut cubic-bezier(0.16, 1, 0.3, 1)` and `easeInOut cubic-bezier(0.65, 0, 0.35, 1)`. Respect `prefers-reduced-motion` / `MediaQuery.disableAnimations`: crossfades only.
- **Key-moment animations** (Rive/Lottie): PR celebration, streak milestone, workout complete, check-in reviewed. Each ≤ 1.5 s, skippable, with a haptic.
- **Haptics** (`haptics.dart`): selection on set ✓, light impact on timer end, success notification on PR, warning on destructive confirm.
- **Touch targets:** `kAuTouchTarget = 44` pt minimum; set-row ✓ is 48.

### Directionality

Logical properties everywhere: Tailwind `ms-*/me-*/ps-*/pe-*/start-*/end-*`, Flutter
`EdgeInsetsDirectional`, `AlignmentDirectional`, `PositionedDirectional`. Icons that imply
direction (back, next, send, progress arrows) mirror in RTL via `AuDirectionalIcon`; icons that
don't (play, checkmark, clock) never mirror. Charts keep time flowing left→right in all locales
(convention in Arabic data visualisation); labels and legends follow reading direction.

## 2. Component inventory

Status reflects the repository today: **Flutter** = `packages/core/lib/src/design/components`,
**Web** = `backend/resources/views/components/ui` (Blade).

| Component | Spec highlights | Flutter | Web |
|---|---|---|---|
| Button | primary (gold), secondary (outline), ghost, destructive; sizes sm 36 / md 44 / lg 52; loading state keeps width; icon start/end | `AuButton`, `AuIconButton` | `x-ui.button` |
| Text input | label above, helper/error below, prefix/suffix, unit suffix (kg/lb); error uses `danger` + icon (not colour alone) | `AuTextField` | `x-ui.input`, `x-ui.field`, `x-ui.textarea` |
| Select / dropdown | searchable when > 8 options; bottom sheet on phones | `AuBottomSheet` + `AuListRow` | `x-ui.select`, `x-ui.dropdown` |
| Stepper | − value +, long-press repeat, keyboard entry; used for reps, kg (2.5 kg steps), servings | `AuStepper` | `x-ui.input` (number) |
| Segmented control | 2–4 options; units, day/week/month | `AuSegmented` | `x-ui.tabs` (pill variant) |
| Toggle / checkbox | 44 pt hit area | `AuControls` | `x-ui.toggle`, `x-ui.checkbox` |
| Card | surface + radius md; optional header, footer actions; pressable | `AuCard` | `x-ui.card` |
| Stat tile | label, value (h2, tabular), delta chip (▲ success / ▼ danger), sparkline | `AuStatTile` | `x-ui.stat-tile` |
| Charts | line (weight), bar (volume), rings (macros), heatmap (cohorts, compliance); tokens for series colours | `AuProgressRing`, `AuMacroRings`, `AuMacroBar` | `x-ui.progress-ring`, chart partials |
| List row | leading avatar/icon, title, subtitle, trailing value/chevron; swipe actions on mobile | `AuListRow`, `AuIconTile` | `x-ui.table` rows |
| Tabs | underline (pages), pill (filters) | `AuSegmented` | `x-ui.tabs` |
| Bottom sheet | drag handle, snap points 50 % / 90 %, keyboard-safe | `AuBottomSheet` | `x-ui.slide-over` (desktop equivalent) |
| Modal / dialog | max one primary action; destructive confirm names the object | `AuOverlays` | `x-ui.modal` |
| Toast | bottom (mobile) / top-end (web), 4 s, undo action where reversible | `AuOverlays` | `x-ui.toasts` |
| Empty state | illustration, one-sentence explanation, **the next action** as a button | `AuEmptyState` | `x-ui.empty-state` |
| Error state | what happened, retry, offline hint | `AuErrorState` | `x-ui.empty-state` (error variant) |
| Skeleton loader | matches final layout; shimmer 1.2 s; never spinners for content | `AuSkeleton`, `AuSkeletonList`, `AuShimmer` | `x-ui.skeleton` |
| Avatar | image → initials fallback on brand-tinted background; status dot | `AuAvatar` | `x-ui.avatar` |
| Badge / chip | count badge, status chip (risk), filter chip (selectable) | `AuBadge`, `AuChip` | `x-ui.badge`, `x-ui.chip` |
| Timer display | mm:ss tabular, ring countdown, end-of-rest haptic | `AuTimerDisplay` | — (web logger is P2) |
| **Set-logger row** | set # · previous (muted) · kg stepper · reps stepper · RPE · ✓ (48 pt); prefilled values shown in `textSecondary` until confirmed; swipe to delete; long-press for set type | `AuSetRow` | — |
| Streak flame | animated flame with count; colour intensifies at 7/30/100 | `AuStreakFlame` | — |
| Section header | overline + title + trailing action | `AuSectionHeader` | — |
| Pagination | cursor "Load more" for feeds; numbered for admin tables | — | `x-ui.pagination` |
| Async wrapper | loading → skeleton, error → error state, data → child | `AuAsync` | Livewire `wire:loading` patterns |

## 3. Key screens (text wireframes)

Wireframes are written LTR; Arabic mirrors horizontally (except charts, media controls and numbers).

### 3.1 Coach dashboard home (web, ≥ 1280 px)

```
┌──────────────┬───────────────────────────────────────────────────────────────────────────┐
│ ◆ AURUM      │  Good morning, Sara                      [✦ Copilot ⌘K]   🔔 3   (SA) ▾  │
│              │───────────────────────────────────────────────────────────────────────────│
│ ▸ Dashboard  │ ┌Active clients┐┌At risk┐┌Check-ins to review┐┌Unread┐┌Sessions today┐┌MRR┐ │
│   Clients    │ │ 42  ▲3       ││ 5 ●   ││ 7                 ││ 12   ││ 6            ││QAR│ │
│   Programs   │ └──────────────┘└───────┘└───────────────────┘└──────┘└──────────────┘│12k│ │
│   Workouts   │ ┌ Needs attention ─────────────────────┐ ┌ Today ─────────────────────────┐ │
│   Exercises  │ │ ● Omar K.  3 days inactive  [Nudge]  │ │ 07:00  Omar K.   1:1  Gym A ✓  │ │
│   Nutrition  │ │ ● Lina M.  score ▼28%       [Nudge]  │ │ 09:30  HIIT class 12/14  ▸     │ │
│   Habits     │ │ ◐ Ali S.   check-in overdue [Open]   │ │ 18:00  Zoom — Julia R.         │ │
│   Check-ins  │ │            [Message all at risk]     │ └────────────────────────────────┘ │
│   Calendar   │ └──────────────────────────────────────┘ ┌ Recent activity ───────────────┐ │
│   Messages   │ ┌ Onboarding ──────────────┐            │ 🏆 Omar hit a PR: Bench 100 kg │ │
│   Community  │ │ ✓ Brand  ✓ First client  │            │ 📝 Lina submitted check-in     │ │
│   Payments   │ │ ○ Connect payments  [Go] │            │ 💳 Ali paid 12-week package    │ │
│   …          │ └──────────────────────────┘            └────────────────────────────────┘ │
└──────────────┴───────────────────────────────────────────────────────────────────────────┘
```
Empty state (no clients): the KPI row is replaced by "Invite your first client — they'll be logging in under 2 minutes" + [Invite] + [Import CSV].

### 3.2 Client "Today" (phone)

```
┌───────────────────────────────┐
│ Sunday, 1 Nov        🔥 5     │
│ Morning, Omar                 │
│ ┌───────────────────────────┐ │
│ │ WEEK 3 · DAY 2            │ │
│ │ Upper Hypertrophy         │ │
│ │ 52 min · 7 exercises      │ │
│ │ [▶ Start workout]  (gold) │ │
│ └───────────────────────────┘ │
│ ┌ Nutrition ───────────────┐  │
│ │ (◯ 1,240/2,200) P C F bars│ │
│ │ 💧 1.2 / 2.5 L   [+250 ml] │ │
│ └──────────────────────────┘  │
│ Habits 2/4  ☑ Water ☐ Steps … │
│ ┌ Check-in due tomorrow [Go]┐ │
│ ┌ Next: 1:1 with Sara 18:00 ┐ │
│ ✉ 3 unread from Sara          │
├───────────────────────────────┤
│ Today  Train  Food  Progress ✉│
└───────────────────────────────┘
```

### 3.3 Workout logger (phone)

```
┌───────────────────────────────┐
│ ✕  Upper Hypertrophy   18:42  │
│ ● Offline · will sync         │
│ ┌ A1 · Bench Press ▸ video ─┐ │
│ │ Set  Prev      kg    Reps ✓│ │
│ │ W    40×10    [40]  [10] ✓ │ │
│ │ 1    80×8     [82.5][8]  ○ │ │  ← prefilled (muted) until ✓
│ │ 2    80×8     [82.5][8]  ○ │ │
│ │ 3    80×7     [82.5][8]  ○ │ │
│ │ [+ Set]  Apply set 1 to all│ │
│ └────────────────────────────┘ │
│ ┌ A2 · Chest-supported Row ─┐ │
│ │ … superset: alternates     │ │
│ └────────────────────────────┘ │
│ ┌ Rest  01:12  ◯─── [−15][+15]│  ← also on lock screen
│ [Finish workout]               │
└───────────────────────────────┘
```

### 3.4 Program builder (web)

```
┌ Library ─────────┬ Week grid ─────────────────────────────────────────┬ Inspector ───────┐
│ 🔍 Search (4 lang)│      Mon      Tue      Wed      Thu    Fri  Sat Sun│ Day 2 · Week 1   │
│ Filters: muscle ▾│ W1  [Upper A] [Lower A]   rest  [Upper B][Lower B]…│ Workout: Upper A │
│ ▸ Workouts       │ W2  [Upper A] [Lower A]   rest  …                  │ Meal plan: —     │
│ ▸ Templates      │ W3  …                                              │ Habits: Water 3L │
│ ▸ Exercises      │ W4  DELOAD ………                                     │ Progression:     │
│                  │ [+ Week]  [Copy week ▾]  [Apply progression]       │  +2.5 kg if all  │
│ drag → grid      │                                                    │  reps hit        │
└──────────────────┴────────────────────────────────────────────────────┴──────────────────┘
 Autosaved · ⌘Z undo · [Preview as client] · [Assign ▾]
```

### 3.5 Nutrition day (phone)

```
┌───────────────────────────────┐
│ ‹  Today, 1 Nov  ›   🌙 Ramadan│
│ (◯ kcal 1,240 / 2,200)        │
│ P ███████░ 110/160 g           │
│ C ████░░░ 120/220 g            │
│ F ███░░░░ 38/70 g              │
│ Iftar 17:12 · Suhoor 04:31 ⏱   │
│ ┌ Iftar ─────────── 620 kcal ┐ │
│ │ Dates (3)          70       │ │
│ │ Lentil soup        180      │ │
│ │ ☐ Planned: Kabsa 300 g      │ │
│ │ [Log from plan]  [+ Add]    │ │
│ └─────────────────────────────┘ │
│ ┌ Suhoor ──────── planned ────┐ │
│ 💧 1.2 / 2.5 L  [+250] [+500]   │
│ [ 🔍 Search ][ ▦ Scan ][ 📷 ]  │
└───────────────────────────────┘
```

### 3.6 Check-in review (web)

```
┌ Lina M. · Weekly check-in · submitted 2 h ago ─────────────────── [Mark reviewed] ┐
│ ┌ Answers ──────────────────────┐ ┌ Trends (8 weeks) ─────────────────────────────┐ │
│ │ Energy 7/10 (▼1)              │ │ Weight  ╲__╱‾╲__  71.2 kg (−0.6)              │ │
│ │ Sleep 6 h                     │ │ Waist   ╲___      78 cm (−1)                  │ │
│ │ Hunger: "hard at night"       │ │ Compliance 82 %  W 90 · N 70 · H 85 · C 100   │ │
│ │ Wins: "hit 3 workouts"        │ └───────────────────────────────────────────────┘ │
│ └───────────────────────────────┘ ┌ Photos ── [front ▾] 25 Oct ◀━━━●━━━▶ 1 Nov ──┐ │
│ ┌ ✦ Copilot draft (Pro) ────────┐ │        before | after compare slider         │ │
│ │ Summary · Risks · Reply (ar)  │ └───────────────────────────────────────────────┘ │
│ │ [Approve] [Edit] [Regenerate] │ ┌ Thread ───────────────────────────────────────┐ │
│ └───────────────────────────────┘ │ Lina: "Can we swap Thursday?"   [Reply…]      │ │
└────────────────────────────────────────────────────────────────────────────────────┘
```

## 4. Content & voice

Warm, precise, never shouty. Sentence case everywhere (no Title Case buttons). Numbers before
adjectives ("3 clients need attention"). Empty states teach the next action. Errors say what
happened and what to do. Arabic copy is written in Modern Standard Arabic by a native writer,
not translated word for word; Gulf colloquial only in marketing headlines where tested.

## 5. Quality gates

- Golden tests for every Flutter component in LTR + RTL × light + dark.
- Lighthouse a11y ≥ 95 and axe checks on marketing and dashboard pages (CI `web-quality.yml`).
- Manual VoiceOver/TalkBack pass in English and Arabic before each major release.
