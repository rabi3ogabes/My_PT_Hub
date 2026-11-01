# Screenshots — Aurum client app (en)

Eight portrait frames, in order. Captions are final copy for `en`; headline ≤ 28 characters, sub-caption ≤ 45.

## Frame 1 — Log workouts in seconds

- **Headline:** Log workouts in seconds
- **Sub-caption:** Last session prefilled. Tap ✓ and go.
- **Screen:** Workout logger mid-session, set 3 of 4, rest timer 1:12
- **Visual:** Thumb tapping the ✓ on a prefilled set row; gold check animates

## Frame 2 — AI builds your plan

- **Headline:** AI builds your plan
- **Sub-caption:** Approved by your coach before you see it
- **Screen:** Program week view with 'Reviewed by Coach Sara' badge
- **Visual:** Week strip with today's workout highlighted

## Frame 3 — Your coach in your pocket

- **Headline:** Your coach in your pocket
- **Sub-caption:** Chat, voice notes and check-ins
- **Screen:** Chat thread with voice note waveform + shared workout card
- **Visual:** Coach avatar top, voice note playing

## Frame 4 — Hit your macros, effortlessly

- **Headline:** Hit your macros, effortlessly
- **Sub-caption:** Log a full meal from your plan in one tap
- **Screen:** Nutrition day: kcal ring 1,840/2,200, macro bars, 'Log from plan' button
- **Visual:** Rings filling in gold / red / blue

## Frame 5 — Scan, snap or search

- **Headline:** Scan, snap or search
- **Sub-caption:** Gulf, Levant, Maghreb and French foods
- **Screen:** Barcode scanner sheet over a food search with regional results
- **Visual:** Camera frame with a product barcode

## Frame 6 — Never lose a workout

- **Headline:** Never lose a workout
- **Sub-caption:** Works offline. Resumes after you close it
- **Screen:** Logger with 'Offline · will sync' pill and lock-screen rest timer
- **Visual:** Split: phone lock screen Live Activity + logger

## Frame 7 — Every PR, celebrated

- **Headline:** Every PR, celebrated
- **Sub-caption:** Records, photos and measurements
- **Screen:** PR celebration overlay over progress charts
- **Visual:** Confetti in champagne gold, weight chart trending down

## Frame 8 — Train in your language

- **Headline:** Train in your language
- **Sub-caption:** العربية · Français · English · Español
- **Screen:** Today screen in Arabic (RTL) next to French
- **Visual:** Two phones, mirrored layouts

## Production spec (all frames)

| Target | Size (portrait) | Notes |
|---|---|---|
| iPhone 6.9" | 1320 × 2868 | required; App Store scales down to 6.5"/6.3" |
| iPhone 6.5" | 1284 × 2778 | legacy slot, same art |
| iPhone 5.5" | 1242 × 2208 | only if older devices are supported |
| iPad 13" | 2064 × 2752 | landscape variants for the coach app builder/calendar |
| Android phone | 1080 × 1920 (min) – 1440 × 3120 | 9:16 to 9:19.5 |
| Android 7" / 10" tablet | 1200 × 1920 / 1600 × 2560 | |

- Source: real app screens captured by `integration_test` screenshot runs (seeded demo tenant, `SEED_DEMO=true`), composited onto the Aurum template: charcoal `#0B0B0F` background, champagne gold `#D4AF6A` headline accents, Manrope 800 (Latin) / IBM Plex Sans Arabic 700 (Arabic), headline ≤ 28 characters on 2 lines max.
- Frames 1–3 carry the top benefits in large type; they are the only frames most shoppers see.
- Device status bar: 9:41, full battery, no carrier; dark mode for frames 1, 2, 6, 8 and light mode for 3, 4, 5, 7 to show both themes.
- Demo data only (flagged demo coach "Coach Sara"); no real client names, faces only from licensed stock with model release.
- Never show prices or competitor names in screenshots.
