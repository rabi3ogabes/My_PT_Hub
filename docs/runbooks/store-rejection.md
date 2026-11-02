# Runbook — App Store / Google Play rejection or removal

**Signals:** App Store Connect "Rejected" / "Metadata Rejected" email; Play Console policy
email, "Rejected" or "Removed" status; Resolution Center message.

## 1. First hour
1. Read the full message in Resolution Center / Play Console *Policy status*; capture guideline numbers and screenshots into the release ticket.
2. Determine scope: binary vs metadata only (metadata fixes need no new build); one app or all white-label apps.
3. Notify #releases; if a hotfix is blocked, consider server-side mitigation (feature flag / Remote Config) — apps keep working.

## 2. Common rejections and fixes
| Guideline / policy | Typical cause in our apps | Fix |
|---|---|---|
| Apple 2.1 App completeness | Reviewer couldn't sign in / empty states | Verify demo accounts (`review.client@`, `review.coach@`) and seeded demo data on production; add step-by-step notes and an invite code |
| Apple 3.1.1 IAP | Purchase CTA for digital-only content visible on iOS | Set affected products to `sell_in_app_ios = hidden` or `iap` (ADR 0011); no external links/buttons for digital content; reply explaining real-world services under 3.1.3(d)/(e) |
| Apple 3.1.3(b) / SaaS | Coach app shows pricing/upgrade CTA | Show plan status only; remove purchase CTA on iOS storefronts that don't allow links |
| Apple 4.2.6 Template apps | White-label binary from Aurum's account | Follow ADR 0010: explain unique content; otherwise move tenant to container model or the coach's own developer account |
| Apple 5.1.1(v) Account deletion | Reviewer can't find deletion | Settings → Privacy → Delete account; mention path in review notes |
| Apple 5.1.1 / 5.1.2 Data use | Purpose strings vague (camera, photos, Health) | Specific purpose strings per permission in all 4 languages |
| Apple 2.5.1 / HealthKit | HealthKit used without clear user-facing feature | Show the "Connected devices" screen and health-based habit; HealthKit data never used for ads (state in notes) |
| Apple 1.2 UGC | Community lacks report/block | Point to report flow and moderation tools; ensure block/mute is reachable from posts and chat |
| Apple 2.3.7 / Play metadata | Competitor names or keyword stuffing | Run `node store/validate.mjs` (trademark + keyword checks); edit listing |
| Play Data safety mismatch | SDK collects something undeclared | Compare with `store/privacy/data_safety.md`; update form; ensure AD_ID permission removed |
| Play Health Connect | Missing permission rationale or declaration | Rationale activity + Health apps declaration (`store/categories_and_ratings.md`) |
| Play account deletion | Web deletion URL missing | `https://aurum.coach/{locale}/legal/delete-account` live and linked |

## 3. Respond
- Reply in Resolution Center within 24 h: concise, factual, numbered changes, screenshots/video of the flow. Template:
  > Thank you for the review. We made the following changes in build {build}: 1) … 2) … To test: sign in with the demo account in App Review Information, then go to Settings → Privacy → Delete account. Real-world coaching services (1:1 sessions, in-person packages) are paid via the coach's payment provider per guideline 3.1.3(d)/(e); digital-only programs are not offered for purchase in the iOS app.
- If you believe the rejection is a misunderstanding, reply with clarification first; use the App Review Board appeal only after one round.
- Play: use *Appeal* in Policy status with the policy name and the fix.

## 4. Release management
- Fix in a `hotfix/*` branch → `mobile-release` beta → resubmit; request expedited review only for a critical bug affecting users.
- Keep Play staged rollout paused while a policy issue is open.
- White-label: one rejection may affect all tenants' binaries — check each app's status in the admin white-label list before resubmitting.

## 5. Close
- Record cause and fix in this runbook's table if new; add a CI check where possible (validator rule, lint).
