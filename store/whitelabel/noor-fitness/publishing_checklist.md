# Publishing checklist — Noor Fitness (published by Aurum)

Mode 1 (included in Studio): the app ships under Aurum's developer account as "Noor Fitness — by Aurum".
## Build
- [ ] `dart run infra/whitelabel/generate.dart noor-fitness --strict` passes (all assets present)
- [ ] Firebase iOS + Android apps created for `coach.aurum.wl.noorfitness` / `coach.aurum.wl.noorfitness`; configs added to the `WL_FIREBASE_CONFIGS_JSON` secret under key `noor-fitness`
- [ ] `noorfitness.aurum.coach` serves `/.well-known/apple-app-site-association` and `/.well-known/assetlinks.json` with the new ids
- [ ] Run **Mobile release → whitelabel** with tenant `noor-fitness` (fastlane `build_whitelabel tenant:noor-fitness`) — builds flavor `noorfitness`
- [ ] Smoke test from TestFlight / Play internal: sign in with an invite code, Arabic RTL, push notification, offline workout

## Store
- [ ] Listing copy reviewed in `store/whitelabel/noor-fitness/*` (title ≤30, subtitle ≤30, short ≤80, keywords ≤100)
- [ ] Screenshots generated from the branded build (8 per locale)
- [ ] Privacy policy live at https://noorfitness.aurum.coach/ar/legal/privacy; privacy labels/data safety copied from `store/privacy/`
- [ ] Review notes include a demo invite code and explain the coach-specific content (guideline 4.2.6)
