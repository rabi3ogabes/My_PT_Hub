# Screenshots — Aurum client app (es)

Eight portrait frames, in order. Captions are final copy for `es`; headline ≤ 28 characters, sub-caption ≤ 45.

## Frame 1 — Registra tu entreno en 3 s

- **Headline:** Registra tu entreno en 3 s
- **Sub-caption:** Pesos de la última vez listos. Toca ✓.
- **Screen:** Registro de entreno, serie 3 de 4, descanso 1:12
- **Visual:** Pulgar marcando ✓ en una serie precargada; check dorado animado

## Frame 2 — La IA crea tu plan

- **Headline:** La IA crea tu plan
- **Sub-caption:** Tu entrenador lo aprueba antes de que lo veas
- **Screen:** Vista semanal con insignia «Revisado por Coach Sara»
- **Visual:** Franja semanal con el entreno de hoy destacado

## Frame 3 — Tu entrenador en el bolsillo

- **Headline:** Tu entrenador en el bolsillo
- **Sub-caption:** Chat, notas de voz y revisiones
- **Screen:** Chat con nota de voz y tarjeta de entreno compartida
- **Visual:** Avatar del entrenador, nota de voz sonando

## Frame 4 — Cumple tus macros sin esfuerzo

- **Headline:** Cumple tus macros sin esfuerzo
- **Sub-caption:** Una comida completa de tu plan con un toque
- **Screen:** Día de nutrición: anillo 1.840/2.200 kcal, barras de macros, botón «Desde el plan»
- **Visual:** Anillos llenándose

## Frame 5 — Escanea, fotografía o busca

- **Headline:** Escanea, fotografía o busca
- **Sub-caption:** Platos de España, Latinoamérica y el Mediterráneo
- **Screen:** Escáner de código de barras sobre resultados regionales
- **Visual:** Marco de cámara sobre un código de barras

## Frame 6 — Nunca pierdas un entreno

- **Headline:** Nunca pierdas un entreno
- **Sub-caption:** Funciona sin conexión y retoma al reabrir
- **Screen:** Registro con aviso «Sin conexión · se sincronizará» y descanso en pantalla de bloqueo
- **Visual:** Pantalla de bloqueo + registro lado a lado

## Frame 7 — Cada récord, celebrado

- **Headline:** Cada récord, celebrado
- **Sub-caption:** Récords, fotos y medidas
- **Screen:** Celebración de récord sobre gráficas de progreso
- **Visual:** Confeti dorado, curva de peso bajando

## Frame 8 — Entrena en tu idioma

- **Headline:** Entrena en tu idioma
- **Sub-caption:** Español · العربية · Français · English
- **Screen:** Pantalla Hoy en español junto a la versión árabe (RTL)
- **Visual:** Dos teléfonos con diseños en espejo

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
