# 0003 — Blade + Livewire over Inertia / SPA

- Status: Accepted · Date: 2026-01-06

## Context
The web surface includes an SEO-critical marketing site with 8,000+ programmatic pages in four
languages, a coach dashboard with rich builders (drag-and-drop workout and program builders,
calendar) and a client portal. The mobile apps (Flutter) consume the REST API; the web does not
need to share a JS component library with them. Core Web Vitals targets: LCP < 2.0 s mobile.

## Decision
Blade + Livewire 4 + Alpine.js + Tailwind 4 for all web UI; Filament 5 for super-admin.
Marketing pages are fully server-rendered and response-cached; interactivity is islands of
Livewire/Alpine. Heavy client-side interactions (builders) use Alpine + small dedicated JS
modules (SortableJS-style drag-and-drop) inside Livewire components.

## Alternatives considered
- **Inertia + Vue/React**: great DX for app-like screens, but SSR adds a Node runtime to
  production and duplicates validation/state; SEO pages would still need SSR tuning.
- **Separate Next.js marketing site**: best-in-class static generation but a second stack,
  duplicated i18n and auth handoff.

## Consequences
- One language (PHP) for business rules and rendering; form validation shared with the API.
- RTL and localization handled once in Blade layouts (`dir="rtl"`, logical utilities).
- Very interactive screens need discipline (wire:model.live sparingly, Alpine for local state)
  to keep latency low; builder autosave goes through debounced Livewire actions.
- CSP must allow Alpine's evaluation in the standard build until we move to the CSP build
  (tracked in SECURITY.md V14.4).
