# User Flows

Eight critical journeys from Phase 0, each with its time target, the API calls behind every
step ([openapi.yaml](openapi.yaml)) and failure paths. Diagrams are Mermaid flowcharts.

Legend: rounded = user action · rectangle = system · diamond = decision · `API` labels name the endpoint.

---

## 1. Coach sign-up → onboarding → first client invite → first program assigned

**Target: < 5 minutes** (KPI: activation = first program **and** first invite within 24 h).

```mermaid
flowchart TD
    A([Coach lands on pricing or home]) --> B([Start free])
    B --> C{Sign-up method}
    C -->|Email + password| D["POST /auth/register<br/>account_type=coach"]
    C -->|Google / Apple| E["POST /auth/social/{provider}"]
    D --> F["TenantProvisioner: tenant, owner membership,<br/>default content, sample client + program flagged demo"]
    E --> F
    F --> G["Welcome email + session tokens"]
    G --> H([Onboarding wizard])
    H --> H1[1. Business name & logo]
    H1 --> H2[2. Brand colour]
    H2 --> H3["3. Currency & country<br/>(prefilled from locale/IP)"]
    H3 --> H4[4. Language & timezone]
    H4 --> H5["5. What do you coach?<br/>(personalizes templates)"]
    H5 --> I["PATCH /tenant · POST /tenant/logo<br/>onboarded_at set"]
    I --> J([Dashboard with onboarding checklist])
    J --> K([Invite first client])
    K --> L{Channel}
    L -->|Email / SMS| M["POST /clients send_invite=true"]
    L -->|Link / QR| N["GET /invitations/{id}/qr"]
    M --> O{Plan limit OK?}
    N --> O
    O -->|No, 4th client on Free| P["402 plan_limit_reached → upgrade sheet<br/>(trial without card)"]
    P --> K
    O -->|Yes| Q[Invitation created, email/WhatsApp sent]
    Q --> R([Pick a program])
    R --> S{Source}
    S -->|Template| T["POST /programs/{id}/duplicate"]
    S -->|AI copilot| U["POST /ai/programs → draft → approve"]
    S -->|Build| V[Program builder]
    T --> W["POST /programs/{id}/assign<br/>client_ids, start_date, unlock_mode"]
    U --> W
    V --> W
    W --> X(["✓ Checklist complete — activation event"])
```

Failure paths: email already used → sign in / magic link offered; social token invalid → 422
with retry; logo upload fails → wizard continues with initials avatar.

---

## 2. Client invite acceptance → onboarding questionnaire → first workout logged

**Target: < 2 minutes** from tapping the invite to the first logged set.

```mermaid
flowchart TD
    A([Client taps invite link or scans QR]) --> B{App installed?}
    B -->|Yes| C["Universal / App Link opens app<br/>/invite/{code}"]
    B -->|No| D["Store page → install<br/>deferred deep link keeps code"]
    D --> C
    C --> E["GET /invitations/{code}<br/>coach name, brand, logo"]
    E --> F{Invitation valid?}
    F -->|Expired or used| G["410 invitation_invalid →<br/>request new invite from coach"]
    F -->|Valid| H{Has account?}
    H -->|No| I["POST /auth/register<br/>account_type=client, invite_code"]
    H -->|Yes| J["POST /auth/login → POST /invitations/accept"]
    I --> K[Membership created, tenant branding applied]
    J --> K
    K --> L([Questionnaire: goal, level, days/week, equipment, injuries, PAR-Q])
    L --> M{PAR-Q flags?}
    M -->|Yes| N["parq_flagged=true → coach notified,<br/>AI guardrails stricter"]
    M -->|No| O
    N --> O([Optional: connect Apple Health / Health Connect])
    O --> P([Language & notifications permission])
    P --> Q["GET /me/today → today's workout card"]
    Q --> R([Start workout])
    R --> S["POST /me/sessions (client ULID)<br/>written to Drift first"]
    S --> T([Tap ✓ on prefilled set])
    T --> U(["✓ First set logged — event client_first_set"])
```

---

## 3. Standalone client: install → goal quiz → free plan → "find a coach" upsell

```mermaid
flowchart TD
    A([Install from store search]) --> B([Onboarding carousel])
    B --> C["Sign up: POST /auth/register<br/>account_type=standalone → personal tenant"]
    C --> D([Goal quiz: goal, experience, days, equipment, minutes, diet])
    D --> E{AI enabled for standalone?}
    E -->|Yes| F["POST /me/ai/starter-plan<br/>within safe templates"]
    E -->|No| G[Rule-based template match]
    F --> H[Free plan assigned]
    G --> H
    H --> I([Train with free plan, habits, nutrition])
    I --> J{Upsell moment?}
    J -->|After 3rd workout / first PR / plateau 2 weeks| K(["Card: Work with a real coach"])
    J -->|Not yet| I
    K --> L([Match quiz: goal, city or online, language, budget, coach gender])
    L --> M["POST /marketplace/match → ranked coaches"]
    M --> N([View coach profile, packages, reviews])
    N --> O{Action}
    O -->|Enquire| P["Lead created in coach CRM<br/>(source=marketplace)"]
    O -->|Buy package| Q["Checkout → see flow 6"]
    O -->|Book consultation| R["Book slot → see flow 7"]
    P --> S([Coach replies in app → invite → becomes coached client])
    Q --> S
    R --> S
```

---

## 4. Workout logging (offline) → sync → coach notified → PR celebration

```mermaid
flowchart TD
    A([Client in gym, no signal]) --> B([Open workout — cached incl. videos])
    B --> C["Create session locally<br/>id = client ULID, status in_progress"]
    C --> D([Set row prefilled from last performance])
    D --> E{Edit?}
    E -->|Same as last| F([Tap ✓])
    E -->|Change set 1| G([Edit → Apply to remaining sets?])
    G --> F
    F --> H["Drift write + SyncQueue mutation<br/>entity=session_set, op=upsert"]
    H --> I[Rest timer: lock screen / ongoing notification, music keeps playing]
    I --> J{More sets?}
    J -->|Yes| D
    J -->|No| K([Finish: effort, mood, notes])
    K --> L["Local complete + mutation<br/>session complete"]
    L --> M{App killed or offline?}
    M -->|Killed| N[Session restored from Drift on reopen]
    N --> K
    M -->|Online now| O
    M -->|Still offline| P[Queue waits: connectivity / resume / periodic worker]
    P --> O["POST /sync/push batched mutations"]
    O --> Q{Per mutation result}
    Q -->|applied| R
    Q -->|duplicate| R[Mark synced]
    Q -->|rejected| S[Keep locally, surface error, retry with backoff]
    R --> T["Server: PR detection on complete<br/>(e1RM, weight, reps, volume)"]
    T --> U{New PR?}
    U -->|Yes| V["Event PersonalRecordAchieved →<br/>push to client + coach, community auto-post optional"]
    U -->|No| W[Coach notified: workout completed]
    V --> X(["Rive celebration + share card"])
    V --> W
```

Conflict policy: server timestamps; last-write-wins per field; session sets append-only;
re-sending the same set id is a no-op (`duplicate`).

---

## 5. Weekly check-in → AI summary → coach approves reply → client notified

```mermaid
flowchart TD
    A["Scheduler: checkin_schedules.next_due_on reached"] --> B["checkin_responses row status=pending<br/>context: last week's metrics, compliance, photos"]
    B --> C[Push + email reminder]
    C --> D{Submitted by due date?}
    D -->|No, +24 h| E["reminded_at → second reminder<br/>+48 h escalated_at → coach notified (overdue)"]
    E --> D
    D -->|Yes| F([Client: step-by-step form, photos with pose overlay, measurements])
    F --> G["POST /me/checkins/{id}/submit"]
    G --> H{AI copilot on plan + feature enabled?}
    H -->|Yes| I["Queued: POST /ai/checkins/{id}/summary<br/>minimum data, PII redacted"]
    H -->|No| L
    I --> J["ai_outputs draft: wins, struggles, trends, risk flags,<br/>reply in coach tone + client language"]
    J --> K{Safety flags?}
    K -->|Injury / ED / extreme deficit| K1[Flag prominently, no suggested diet changes]
    K -->|None| L
    K1 --> L([Coach opens review: answers + trends + photo compare + draft])
    L --> M{Coach decision}
    M -->|Approve as is| N["POST /ai/outputs/{id}/approve"]
    M -->|Edit then approve| N
    M -->|Reject / write own| O["POST /ai/outputs/{id}/reject"]
    N --> P["POST /checkins/{id}/review coach_feedback"]
    O --> P
    P --> Q[Status reviewed, comment in check-in thread]
    Q --> R(["Client push: Your coach replied → opens thread"])
```

Invariant: no AI text reaches the client without `ai_outputs.status in (approved, edited)` by a staff user.

---

## 6. Client buys package → payment → auto-assign content → welcome message

**Target: content assigned < 10 s after payment confirmation.**

```mermaid
flowchart TD
    A([Client opens Shop or coach profile package]) --> B{iOS and digital-only product?}
    B -->|sell_in_app_ios=hidden| B1[Product not shown on iOS]
    B -->|=iap| B2[StoreKit purchase → server receipt validation]
    B -->|external / not iOS / physical service| C
    C["POST /me/shop/{product}/checkout<br/>Idempotency-Key"] --> D["Order pending; gateway chosen by currency<br/>(Tap, Stripe Connect, PayTabs, …)"]
    D --> E([In-app browser: hosted checkout, Apple Pay / mada / card])
    E --> F{Gateway result}
    F -->|Cancelled / failed| G[Order failed → retry or other method]
    F -->|Paid| H["Gateway webhook → signature verified<br/>webhook_events dedupe (driver, external_id)"]
    B2 --> H
    H --> I[Payment succeeded → Order paid → PaymentSucceeded event]
    I --> J["FulfilOrder job (queue: payments)"]
    J --> K[Assign program / meal plan / habits / check-in schedule]
    J --> L[Grant session credits to credit_ledger]
    J --> M[Add to group & community space]
    J --> N[Start conversation + welcome message template]
    J --> O[Invoice issued with sequential number + tax]
    K --> P["fulfilled_at set → coach notified"]
    L --> P
    M --> P
    N --> P
    O --> P
    E -.->|App polls| Q["GET /public/orders/{id}/status"]
    P --> Q
    Q --> R(["Client sees: You're in! → Today shows new program"])
```

Manual payments (`POST /manual-payments`) enter at "Payment succeeded" and run the same pipeline.

---

## 7. Booking a session → credit deduction → reminders → attendance → no-show rule

```mermaid
flowchart TD
    A([Client opens Calendar]) --> B{Type}
    B -->|1:1| C["GET /me/slots?coach_id&date<br/>availability minus busy (incl. Google/Outlook)"]
    B -->|Class| D["GET /me/classes?from&to"]
    C --> E(["Pick slot → POST /me/bookings {coach_id, starts_at}"])
    D --> F(["Pick class → POST /me/bookings {event_id}"])
    E --> G{Credits required and balance ok?}
    F --> G
    G -->|No credits| H[Offer packages → flow 6]
    G -->|Yes| I{Capacity left?}
    I -->|No| J["status=waitlisted, waitlist_position"]
    I -->|Yes| K["status=booked, lock + unique constraint prevent double booking"]
    J --> J1{Someone cancels}
    J1 -->|Yes| J2["Offer to next: push, claim window"]
    J2 --> J3(["POST /me/bookings/{id}/claim"])
    J3 --> K
    K --> L[ICS + confirmation; external calendar event created]
    L --> M["Reminders: 24 h and 2 h (push / email / WhatsApp)"]
    M --> N{Client cancels?}
    N -->|Before cancel_window_hours| O[cancelled, no credit used]
    N -->|Inside window| P["late_cancel → credit forfeited per policy<br/>(ledger entry reason=late_cancel)"]
    N -->|No| Q([Session time: QR check-in or coach marks])
    Q --> R{Attended?}
    R -->|Yes| S["attended → ledger -credits_cost (reason=attended)"]
    R -->|No| T["no_show → forfeit credit or fee per no_show_policy"]
    S --> U[Coach can correct retroactively — bulk status, ledger reversal entries]
    T --> U
    U --> V([Calendar stays on the selected week])
```

Ledger invariant: balance = Σ entries; corrections are new entries, never edits.

---

## 8. Coach cancels subscription (two taps) → confirmation → data retention → export

```mermaid
flowchart TD
    A([Settings → Billing]) --> B([Tap 1: Cancel subscription])
    B --> C["Sheet: what happens next, optional reason,<br/>offer pause or downgrade (never a dark pattern)"]
    C --> D([Tap 2: Confirm cancellation])
    D --> E["POST /billing/cancel"]
    E --> F{Interval}
    F -->|Monthly| G["cancel_at_period_end=true<br/>access until current_period_end"]
    F -->|Annual| H["Immediate cancel + pro-rata refund<br/>via gateway, credit note issued"]
    G --> I[Confirmation email + in-app receipt]
    H --> I
    I --> J{Changes mind before period end?}
    J -->|Yes| K["POST /billing/resume"]
    J -->|No| L["Period ends → tenant.plan=free<br/>(scheduler billing:expire-ended)"]
    L --> M["Data retained: nothing deleted;<br/>over-limit clients become read-only, coach picks 3 active"]
    M --> N{Coach wants to leave entirely?}
    N -->|Export| O["POST /me/export → zip of JSON + media emailed (24 h link)"]
    N -->|Delete| P["DELETE /me → 30-day grace, then erasure;<br/>clients notified and offered to keep their own data"]
    N -->|Stay on Free| Q([Continue with 3 active clients])
    O --> Q
```

Retention policy: Free-plan tenants keep data indefinitely while active; tenants inactive for
24 months are warned 30 and 7 days before archival. See [SECURITY.md](SECURITY.md#4-privacy--data-protection).
