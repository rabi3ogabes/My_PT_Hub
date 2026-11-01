# 0010 — Container app by default; white-label binaries only where policy allows (Apple 4.2.6)

- Status: Accepted · Date: 2026-01-10

## Context
Studio coaches want "their own app". App Store Review Guideline **4.2.6** rejects apps created
from a commercialized template or app-generation service unless they are submitted directly by
the provider of the app's content (i.e. the business itself). Many coaching platforms have had
batches of branded apps removed. Google Play's policies on repetitive content are similar
in spirit. We must not put the main Aurum apps' standing at risk.

## Decision
1. **Default — container model.** One Aurum client app; after the client enters an invite code
   (or opens an invite link), the app applies the coach's branding (name, logo, colours,
   welcome content) at runtime from the tenant. Included in every plan; Studio unlocks full
   theming. No new binary, no review risk.
2. **Studio branded binary, published by Aurum** (mode `aurum_account`): only when the tenant has
   substantial unique content and functionality configuration, named "Coach Name — by Aurum".
   Built by `infra/whitelabel/generate.dart` + Fastlane `build_whitelabel`. Submitted
   individually with review notes explaining the coach-specific content. If Apple rejects under
   4.2.6, the tenant falls back to the container model or mode 3; we do not appeal repeatedly.
3. **Full white label under the coach's own developer account** (mode `own_account`, Enterprise
   or Studio add-on): the coach's organization (D-U-N-S) is the provider of content and submits;
   Aurum builds and uploads as App Manager. This is the 4.2.6-compliant route for true
   white-label on iOS.

## Alternatives considered
- **One binary per coach under Aurum's account for all Studio tenants**: highest perceived
  value, highest rejection and account-risk.
- **No branded apps**: simplest; loses a key Studio selling point and Android opportunity, where
  policy is more permissive.

## Consequences
- The client app must support runtime theming and tenant lock (`TENANT_ID` dart-define for
  binaries, invite-driven for the container).
- White-label flavors share one codebase; every core release regenerates all flavors (`--all`).
- Sales and pricing pages describe mode 1 as "your brand inside Aurum" and modes 2/3 explicitly,
  without promising App Store approval.
