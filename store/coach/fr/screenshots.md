# Screenshots — Aurum Coach app (fr)

Eight portrait frames, in order. Captions are final copy for `fr`; headline ≤ 28 characters, sub-caption ≤ 45.

## Frame 1 — Votre activité de coach, une seule app

- **Headline:** Votre activité de coach, une seule app
- **Sub-caption:** Programmes, nutrition, bilans, paiements
- **Screen:** Tableau de bord : KPI (clients actifs, MRR), clients à risque, séances du jour
- **Visual:** Tuiles KPI dorées sur fond anthracite

## Frame 2 — L'IA propose. Vous validez.

- **Headline:** L'IA propose. Vous validez.
- **Sub-caption:** Programmes et réponses dans votre ton
- **Screen:** Revue d'un brouillon du copilote : comparaison avec Accepter / Modifier / Régénérer
- **Visual:** Barre « ✦ Copilote » ouverte

## Frame 3 — Des programmes en minutes

- **Headline:** Des programmes en minutes
- **Sub-caption:** Supersets, Tabata, EMOM, AMRAP
- **Screen:** Éditeur de séance avec un superset et un bloc Tabata
- **Visual:** Glisser-déposer en cours

## Frame 4 — Repérez les clients à risque

- **Headline:** Repérez les clients à risque
- **Sub-caption:** Score d'assiduité et relance en un geste
- **Screen:** Tableau d'assiduité rouge/orange/vert et message groupé
- **Visual:** Badges rouges qui pulsent

## Frame 5 — Les bilans côte à côte

- **Headline:** Les bilans côte à côte
- **Sub-caption:** Réponses, tendances et photos réunies
- **Screen:** Revue de bilan : comparateur photo + courbe de poids + fil de commentaires
- **Visual:** Curseur avant/après à 50 %

## Frame 6 — Encaissez automatiquement

- **Headline:** Encaissez automatiquement
- **Sub-caption:** Packs, crédits, paiements locaux
- **Screen:** Liste de produits + toast « Paiement reçu → programme assigné »
- **Visual:** Feuille Apple Pay, état succès

## Frame 7 — Réservations sans paperasse

- **Headline:** Réservations sans paperasse
- **Sub-caption:** Liste d'attente, crédits, pointage groupé
- **Screen:** Agenda semaine avec cours, jauges de places et « Marquer présents »
- **Visual:** La semaine reste sélectionnée

## Frame 8 — Français, arabe, anglais, espagnol

- **Headline:** Français, arabe, anglais, espagnol
- **Sub-caption:** Vos clients, dans leur langue
- **Screen:** Tableau de bord en français à côté de l'arabe (RTL)
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
