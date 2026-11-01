# Screenshots — Aurum client app (fr)

Eight portrait frames, in order. Captions are final copy for `fr`; headline ≤ 28 characters, sub-caption ≤ 45.

## Frame 1 — Notez vos séances en 3 s

- **Headline:** Notez vos séances en 3 s
- **Sub-caption:** Charges préremplies. Un ✓ et c'est parti.
- **Screen:** Saisie de séance, série 3/4, minuteur de repos 1:12
- **Visual:** Pouce qui valide une série préremplie ; coche dorée animée

## Frame 2 — L'IA prépare votre programme

- **Headline:** L'IA prépare votre programme
- **Sub-caption:** Validé par votre coach avant de vous parvenir
- **Screen:** Vue semaine du programme avec badge « Validé par Coach Sara »
- **Visual:** Bandeau semaine, séance du jour en surbrillance

## Frame 3 — Votre coach dans la poche

- **Headline:** Votre coach dans la poche
- **Sub-caption:** Messages, notes vocales et bilans
- **Screen:** Fil de discussion avec note vocale et carte séance partagée
- **Visual:** Avatar du coach, note vocale en lecture

## Frame 4 — Vos macros, sans effort

- **Headline:** Vos macros, sans effort
- **Sub-caption:** Un repas complet de votre plan en un geste
- **Screen:** Journée nutrition : anneau 1 840/2 200 kcal, barres de macros, bouton « Depuis le plan »
- **Visual:** Anneaux qui se remplissent

## Frame 5 — Scannez, photographiez, cherchez

- **Headline:** Scannez, photographiez, cherchez
- **Sub-caption:** Couscous royal, tajine, ratatouille…
- **Screen:** Scanner de code-barres au-dessus de résultats de plats maghrébins et français
- **Visual:** Cadre caméra sur un code-barres

## Frame 6 — Plus jamais de séance perdue

- **Headline:** Plus jamais de séance perdue
- **Sub-caption:** Fonctionne hors ligne, reprend après fermeture
- **Screen:** Saisie avec pastille « Hors ligne · synchronisation » et minuteur sur écran verrouillé
- **Visual:** Écran verrouillé + saisie côte à côte

## Frame 7 — Chaque record célébré

- **Headline:** Chaque record célébré
- **Sub-caption:** Records, photos et mensurations
- **Screen:** Célébration de record au-dessus des courbes de progression
- **Visual:** Confettis dorés, courbe de poids en baisse

## Frame 8 — Dans votre langue

- **Headline:** Dans votre langue
- **Sub-caption:** Français · العربية · English · Español
- **Screen:** Écran Aujourd'hui en français à côté de l'arabe (RTL)
- **Visual:** Deux téléphones, mises en page miroir

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
