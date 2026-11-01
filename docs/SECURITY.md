# Security & Privacy

Target: **OWASP ASVS 4.0.3 Level 2** for the API, web and mobile apps; GDPR, **Qatar PDPPL**
(Law No. 13 of 2016) and **KSA PDPL** (Royal Decree M/19, as amended) alignment for health data.

Report vulnerabilities to **security@aurum.coach** (PGP key on `aurum.coach/.well-known/security.txt`).
We acknowledge within 2 business days and aim to fix critical issues within 7 days. Please do
not test against other customers' data; use a free trial tenant.

Status legend: ✅ implemented in the repository · 🟡 partially implemented / configured outside code · ⬜ planned (owner + phase).

## 1. ASVS L2 checklist mapped to implementation

### V1 Architecture, design & threat modelling
| Req | Control | Status | Implementation |
|---|---|---|---|
| 1.1.2 | Threat model for key flows | 🟡 | Flows in [USER_FLOWS.md](USER_FLOWS.md); STRIDE review per phase recorded in PR descriptions; formal threat model doc before pentest |
| 1.2.x | Least-privilege components | ✅ | Containers run non-root (`www-data`); DB user without superuser; separate buckets for public/private media |
| 1.4.1 | Trusted enforcement points | ✅ | All authorization server-side (policies, `EnsureRole`, tenant scopes); apps never trusted |
| 1.5.x | Input/output architecture | ✅ | FormRequests / `$request->validate`; Resources shape output; envelope via `ApiController` |
| 1.6.x | Cryptographic architecture | ✅ | `APP_KEY` from secret manager; `APP_PREVIOUS_KEYS` supports rotation; encrypted casts for sensitive columns |
| 1.14.x | Configuration segregation | ✅ | Env-only secrets; `.env` never in images (`rm -f .env` in Dockerfile); staging/prod GitHub environments |

### V2 Authentication
| Req | Control | Status | Implementation |
|---|---|---|---|
| 2.1.1 | Passwords ≥ 8 chars | ✅ | `Password::min(8)->letters()->numbers()` on register/reset/update |
| 2.1.7 | Breached-password check | ⬜ | Add `->uncompromised()` (HIBP k-anonymity) — Phase 9 hardening |
| 2.2.1 | Anti-automation | ✅ | `throttle:auth` 10/min per IP **and** per account; lockout after 5 failures for 15 min (`users.failed_logins`, `locked_until`, 423 `account_locked`) |
| 2.2.3 | Notify on security events | 🟡 | Email on password change and 2FA change; new-device sign-in email ⬜ |
| 2.4.1 | Password storage | ✅ | bcrypt, `BCRYPT_ROUNDS=12` |
| 2.5.x | Recovery | ✅ | Password reset tokens (Laravel broker, 60 min); magic links hashed (`magic_links.token_hash`), 15 min, single use |
| 2.7/2.8 | MFA | ✅ / 🟡 | TOTP enrol/confirm/challenge/disable; secrets + recovery codes encrypted. Enforcement for owners and super-admins on billing/payments routes ⬜ (Phase 9) |
| 2.10.x | Service credentials | ✅ | Gateway credentials, integration tokens, webhook secrets encrypted; API tokens hashed (Sanctum sha256) |

### V3 Session management
| Req | Control | Status | Implementation |
|---|---|---|---|
| 3.2.x | Token generation | ✅ | Sanctum tokens (random, hashed at rest) |
| 3.3.1 | Logout invalidates | ✅ | `POST /auth/logout` deletes access + refresh tokens for the device |
| 3.3.2 | Idle/absolute timeouts | ✅ | Access 60 min; refresh 60 days absolute; web session 120 min, encrypted, Redis |
| 3.3.4 | Token rotation | ✅ / ⬜ | Refresh rotates the pair and revokes the used token; refresh **reuse detection** revoking the whole device family ⬜ |
| 3.4.x | Cookie flags | ✅ | `HttpOnly`, `SameSite=Lax`, `Secure` in production (`SESSION_SECURE_COOKIE`) |
| 3.5.x | Token storage on device | ✅ | `flutter_secure_storage` (Keychain / Keystore); biometric lock option |

### V4 Access control
| Req | Control | Status | Implementation |
|---|---|---|---|
| 4.1.1 | Server-side enforcement | ✅ | `auth:sanctum` + `ability:access`, `tenant`, `role:*` middleware on route groups |
| 4.1.3 | Least privilege | ✅ | Roles owner/coach/assistant/client; assistant permissions via spatie (teams = tenant) |
| 4.2.1 | IDOR protection | ✅ | Tenant resolved before route-model binding → foreign ids 404; clients restricted to `client_id = auth()->id()`; child tables reached via scoped parents |
| 4.2.2 | CSRF | ✅ | Laravel CSRF on web; API is token-based; webhooks excluded and signature-verified |
| 4.3.1 | Admin interfaces | 🟡 | Filament restricted to super-admins; IP allow-list at edge for `/admin` and `/horizon` ⬜ |
| 4.3.2 | Directory browsing / metadata | ✅ | nginx denies dotfiles, non-index PHP and source maps |
| — | Cross-tenant tests | ✅ | Required per module (CONVENTIONS); present for implemented modules in `backend/tests/Feature/*` |

### V5 Validation, sanitization & encoding
| Req | Control | Status | Implementation |
|---|---|---|---|
| 5.1.x | Input validation | ✅ | Validation on every write; enums via `in:`; ULIDs validated; only validated data reaches models |
| 5.2.x | Sanitization | ✅ | Rich text (community, CMS) sanitized to an allow-list on save; Markdown rendered safely |
| 5.3.x | Output encoding | ✅ | Blade `{{ }}` escaping; JSON responses; PDF generation from escaped templates |
| 5.3.4 | SQL injection | ✅ | Eloquent/query builder bindings only; AI business assistant uses predefined tool functions, never model-written SQL |
| 5.5.x | Deserialization | ✅ | No `unserialize` of user input; queued jobs signed by Laravel |

### V7 Error handling & logging
| Req | Control | Status | Implementation |
|---|---|---|---|
| 7.1.x | No sensitive data in logs | ✅ | Passwords/tokens never logged; Sentry PII scrubbing; message bodies encrypted |
| 7.2.x | Security event logging | ✅ | `ActivityLogger` → `activity_logs` (causer, impersonator, IP, UA) for auth, billing, impersonation, exports, deletions |
| 7.3.x | Log protection | 🟡 | JSON logs shipped to central store with 90-day retention; append-only bucket ⬜ |
| 7.4.1 | Generic errors | ✅ | `ErrorRenderer` returns envelope `code`/`message` without stack traces when `APP_DEBUG=false` |

### V8 Data protection
| Req | Control | Status | Implementation |
|---|---|---|---|
| 8.1.x | Sensitive data not cached | ✅ | `Cache-Control: no-store` on authenticated API responses; CDN caches only marketing + public media |
| 8.2.x | Client-side data | ✅ | Drift DB in app sandbox; tokens in secure storage; option to wipe on sign-out |
| 8.3.x | Sensitive data at rest | ✅ | Encrypted casts: `messages.body`, `users.two_factor_*`, `client_profiles.date_of_birth/injuries/medical_notes`, `payment_gateways.credentials`, `integrations.*_token`, `webhook_endpoints.secret`; volume/bucket encryption |
| 8.3.4 | Health data minimisation | ✅ | AI requests send minimum fields, no names/emails; analytics events carry no health values |
| 8.3.7 | Media privacy | ✅ | Progress photos/attachments on private storage, 10-minute signed URLs after ownership check |

### V9 Communications
| Req | Control | Status | Implementation |
|---|---|---|---|
| 9.1.x | TLS everywhere | ✅ | Caddy/Cloudflare TLS 1.2+; HSTS `max-age=63072000; includeSubDomains; preload`; target SSL Labs A+ |
| 9.2.x | Outbound TLS | ✅ | All third-party calls over HTTPS with certificate validation |
| — | Certificate pinning (apps) | ⬜ | Evaluate for API host after launch (risk of bricking on rotation) |

### V10 Malicious code / V14 Configuration & dependencies
| Req | Control | Status | Implementation |
|---|---|---|---|
| 10.3.x | Integrity of updates | ✅ | Signed store builds; image provenance + SBOM attestations (`deploy.yml`) |
| 14.2.x | Dependency management | ✅ | Dependabot (composer, npm, pub, bundler, docker, actions); `composer audit` + `npm audit` in CI; Trivy image scan blocks critical CVEs |
| 14.3.x | Debug off in production | ✅ | `APP_DEBUG=false` baked into the production image; Telescope not installed |
| 14.4.x | Security headers | ✅ / 🟡 | nginx + `SecurityHeaders`: HSTS, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, COOP/CORP; CSP enforced in production (`infra/docker/nginx/snippets/csp.conf`) — currently allows `'unsafe-inline' 'unsafe-eval'` for Livewire/Alpine; nonce-based CSP with Alpine CSP build ⬜ |
| 14.5.x | HTTP method / host validation | ✅ | Explicit route methods; trusted proxies; host allow-list at edge |

### V11 Business logic · V12 Files · V13 API
| Req | Control | Status | Implementation |
|---|---|---|---|
| 11.1.x | Business limits & anti-abuse | ✅ | Plan limits (`PlanGate`), AI caps per tenant, `throttle:ai` 20/min, booking capacity under lock + unique constraints |
| 11.1.4 | Replay protection on money | ✅ | `Idempotency-Key` middleware (24 h replay, in-progress lock 409); webhook dedupe `webhook_events` |
| 12.1.x | Upload limits | ✅ | `client_max_body_size 520m` at nginx, per-plan video quotas, MIME + extension validation, FFmpeg probes before processing |
| 12.4.x | Untrusted files stored outside webroot | ✅ | Object storage private bucket; never executed |
| 12.6.1 | SSRF | ✅ | Outbound webhooks: https only, private IP ranges blocked at delivery, 10 s timeout; barcode lookup only to configured OFF host |
| 13.1.x | API security | ✅ | Bearer auth, consistent envelope, rate limits per group (api 120/min, auth, ai, public 30/min) |
| 13.2.x | RESTful verbs & content types | ✅ | JSON only (`Accept: application/json`), multipart only on upload endpoints |
| 13.2.6 | Webhook authenticity | ✅ | Inbound: provider signature verification per driver; outbound: HMAC-SHA256 signature header with endpoint secret |

### Mobile (MASVS-aligned)
| Control | Status | Implementation |
|---|---|---|
| Secure storage of tokens | ✅ | Keychain / EncryptedSharedPreferences via `flutter_secure_storage` |
| Code obfuscation & symbols | ✅ | `--obfuscate --split-debug-info` in Fastlane builds; symbols uploaded to Crashlytics |
| No secrets in binaries | ✅ | Only public config via `--dart-define-from-file`; API keys server-side |
| Screenshot protection on sensitive screens | ⬜ | Progress photo compare + check-in photos (`FLAG_SECURE` / iOS blur on background) |
| Jailbreak/root detection | ⬜ | Informational only (no blocking) — evaluate post-launch |

## 2. Operational security

- **Secrets:** GitHub environments (staging/production) + host secret store (`/opt/aurum/shared/.env`, root-only, provisioned by Forge). No secrets in the repo; Dependabot and secret scanning enabled. Goal: GitHub OIDC → cloud secret manager.
- **Access:** SSH by key only, deploy user without sudo except Docker; production DB reachable only from app hosts; break-glass access logged.
- **Impersonation:** super-admin only, banner shown, `activity_logs.impersonator_id` recorded, sessions expire after 30 minutes.
- **Backups:** encrypted PITR + nightly dumps; quarterly restore drill ([runbook](runbooks/restore-drill.md)).
- **Pentest:** external test before public launch; critical/high fixed before GA (LAUNCH.md T-2w gate).
- **Incident response:** [outage runbook](runbooks/outage.md); breach notification within 72 h (GDPR art. 33), PDPPL/PDPL regulator notification as required; affected users informed without undue delay.

## 3. Data inventory (summary)

Full column-level classification is generated into [ERD.md](ERD.md) (flags PII / HEALTH / SECRET / ENCRYPTED).

| Category | Examples | Lawful basis (GDPR) | Retention |
|---|---|---|---|
| Account | name, email, phone, locale | Contract | Life of account + 30 days |
| Health & fitness (special category) | body metrics, check-ins, injuries, medical notes, food logs, workouts, health samples, progress photos | **Explicit consent** (art. 9(2)(a)) captured at onboarding, withdrawable; for coaches as controllers of their clients: Aurum acts as processor under the DPA | Life of coaching relationship; deleted with account or on request |
| Communications | messages, voice notes, attachments | Contract | Life of conversation / account |
| Payments | orders, invoices, subscription status | Contract, legal obligation (tax: 10 years FR, 6 years KSA ZATCA) | Statutory periods |
| Usage & diagnostics | analytics events, crash logs | Legitimate interest (opt-out in settings) | 14 months analytics, 90 days crash logs |
| Marketing | newsletter, leads | Consent | Until withdrawn |

## 4. Privacy & data protection

### Roles
- **Aurum is controller** for coach accounts and platform data, and **processor** for client data that a coach (controller) collects about their clients. The DPA (`/legal/dpa`) governs processing, subprocessors and assistance with data-subject requests.
- Standalone clients (no coach): Aurum is controller.

### GDPR (EU — France, Spain, Belgium launch markets)
- Lawful bases as above; explicit consent for health data; consent records stored with timestamp and version.
- Data-subject rights: access/portability (`POST /me/export` → JSON + media zip), rectification (profile), erasure (`DELETE /me`, 30-day grace, then anonymisation; backups roll off in 35 days), restriction/objection (support), withdrawal of consent (settings → privacy).
- Privacy by design: minimisation in AI prompts, encrypted special-category columns, private-by-default photos, no advertising SDKs, no IDFA/AAID.
- International transfers: EU tenants hosted in EU (Hetzner Germany/Finland); processors outside the EU covered by SCCs / EU-US DPF; transfer impact assessments on file.
- DPIA completed for health data processing and AI features (reviewed annually).
- Cookie banner with reject-all parity; analytics only after consent on web.

### Qatar PDPPL (Law No. 13 of 2016)
- Health data is "personal data of a special nature": processing requires permission from the competent department (Ministry) or explicit consent; we rely on explicit consent and keep records.
- Data subjects' rights to access, correct, object and withdraw are served by the same export/delete tooling.
- Breach notification to the competent department and affected individuals when the breach may cause serious damage.
- Cross-border transfer: permitted unless it violates the law or causes serious damage; GCC tenants can be pinned to a GCC region stack for Enterprise.
- Children's data: product not directed at children (minimum age 16, see `store/categories_and_ratings.md`).

### KSA PDPL (Royal Decree M/19 of 1443H, amended M/148 of 1444H) & SDAIA implementing regulations
- Health data is sensitive data: explicit consent, purpose limitation, and processing records maintained.
- Transfers outside the Kingdom only under the Transfer Regulation conditions (adequacy, appropriate safeguards such as SCC-equivalents, or exceptions); in-Kingdom hosting option evaluated for Enterprise (PRODUCT_SPEC §9).
- Data Protection Officer appointed when processing sensitive data at scale; registration on SDAIA's national portal when required.
- Breach notification to SDAIA within 72 hours of awareness where the breach may harm data subjects.
- Records of processing activities, privacy notice in Arabic, and retention schedule maintained.

### Subprocessors (published at `/legal/subprocessors`)
Hetzner (hosting, EU), Cloudflare (CDN/WAF), managed Postgres/Redis provider (EU region), object storage (EU), Google Firebase (push, crash, analytics), Sentry (errors), Anthropic and OpenAI (AI drafts; zero-retention / no-training), Postmark or Amazon SES (email), Meta WhatsApp Business (opt-in reminders), Stripe (SaaS billing). Client payment gateways are the coach's own processors.

## 5. Security testing in CI

| Check | Where |
|---|---|
| Pint, Larastan level 8, Pest incl. cross-tenant tests | `.github/workflows/backend.yml` |
| `composer audit`, `npm audit` | `backend.yml` (audit job) |
| OpenAPI completeness | `node infra/scripts/openapi/generate.mjs --check` |
| Image vulnerability scan (Trivy, fail on critical) | `deploy.yml` |
| SBOM + build provenance attestation | `deploy.yml` |
| `flutter analyze --fatal-infos`, tests | `mobile.yml` |
| Dependabot updates (weekly, 5-day cooldown) | `.github/dependabot.yml` |
| Load/latency budget | `infra/k6/api-smoke.js` |
