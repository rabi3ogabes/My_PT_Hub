# Screenshots — Aurum Coach app (es)

Eight portrait frames, in order. Captions are final copy for `es`; headline ≤ 28 characters, sub-caption ≤ 45.

## Frame 1 — Tu negocio de entrenador, en una app

- **Headline:** Tu negocio de entrenador, en una app
- **Sub-caption:** Programas, nutrición, revisiones, cobros
- **Screen:** Panel: KPI (clientes activos, MRR), clientes en riesgo, sesiones de hoy
- **Visual:** Tarjetas KPI doradas sobre carbón

## Frame 2 — La IA propone. Tú apruebas.

- **Headline:** La IA propone. Tú apruebas.
- **Sub-caption:** Programas y respuestas con tu tono
- **Screen:** Revisión de borrador del copiloto: comparación con Aceptar / Editar / Regenerar
- **Visual:** Barra «✦ Copiloto» abierta

## Frame 3 — Programas en minutos

- **Headline:** Programas en minutos
- **Sub-caption:** Superseries, Tabata, EMOM, AMRAP
- **Screen:** Editor de entrenos con una superserie y un bloque Tabata
- **Visual:** Arrastrar y soltar en curso

## Frame 4 — Detecta clientes en riesgo

- **Headline:** Detecta clientes en riesgo
- **Sub-caption:** Puntuación de cumplimiento y aviso en un toque
- **Screen:** Panel de cumplimiento rojo/ámbar/verde y mensaje masivo
- **Visual:** Insignias rojas pulsando

## Frame 5 — Revisiones lado a lado

- **Headline:** Revisiones lado a lado
- **Sub-caption:** Respuestas, tendencias y fotos juntas
- **Screen:** Revisión: comparador de fotos + curva de peso + comentarios
- **Visual:** Deslizador antes/después al 50 %

## Frame 6 — Cobra automáticamente

- **Headline:** Cobra automáticamente
- **Sub-caption:** Paquetes, bonos y pasarelas locales
- **Screen:** Lista de productos + aviso «Pago recibido → programa asignado»
- **Visual:** Hoja de Apple Pay, estado de éxito

## Frame 7 — Reservas sin papeleo

- **Headline:** Reservas sin papeleo
- **Sub-caption:** Lista de espera, créditos, asistencia masiva
- **Screen:** Agenda semanal con clases, aforo y «Marcar asistencia»
- **Visual:** La semana sigue seleccionada

## Frame 8 — Español, árabe, francés, inglés

- **Headline:** Español, árabe, francés, inglés
- **Sub-caption:** Tus clientes, en su idioma
- **Screen:** Panel en español junto a la versión árabe (RTL)
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
