# Aurum for Zapier (and Make)

Official Zapier app definition (`zapier-platform-core`) for Aurum. Included in the Pro plan at no extra cost.

## What it does

| Type | Key | Aurum event / endpoint |
|---|---|---|
| Trigger (REST hook) | `new_client` | `client.created` |
| Trigger (REST hook) | `payment` | `payment.succeeded` |
| Trigger (REST hook) | `checkin_submitted` | `checkin.submitted` |
| Trigger (REST hook) | `workout_completed` | `workout.completed` |
| Trigger (REST hook) | `lead_created` | `lead.created` |
| Action | `create_client` | `POST /api/v1/clients` |
| Action | `assign_program` | `POST /api/v1/programs/{id}/assign` |
| Action | `send_message` | `POST /api/v1/conversations` then `POST /api/v1/conversations/{id}/messages` |
| Action | `add_lead` | `POST /api/v1/leads` |

Hidden triggers `client_list` and `program_list` power the dropdowns in the actions.

## Authentication

Coaches create a **personal access token** in Aurum → Settings → Developer → API tokens
(`POST /api/v1/api-tokens`). The token is shown once. It is tied to the business it was created in
(Aurum pins the tenant from the token), so no `X-Tenant` header is needed.

Required scopes:

- `webhooks:manage`: triggers subscribe and unsubscribe their webhook endpoint
- `clients:read`, `clients:write`: create clients and the client dropdown
- `programs:read`, `programs:write`: assign programs and the program dropdown
- `messages:write`: send messages
- `leads:write`: add leads

A token without the scope a call needs gets `403 forbidden` with the missing scope in the message.

## How the triggers work (REST hooks)

- **Subscribe:** `POST /api/v1/webhook-endpoints {url: <Zapier target URL>, events: ["<event>"]}`. The response `id` is stored as `subscribeData.id`.
- **Unsubscribe:** `DELETE /api/v1/webhook-endpoints/{id}`.
- **Delivery body:**
  ```json
  {"id": "<delivery ULID>", "event": "workout.completed", "created_at": "2026-11-01T09:00:00+00:00",
   "tenant_id": "<business ULID>", "data": { ... }}
  ```
  The trigger flattens `data` into the Zap output and keeps `id`, `event` and `created_at`.
- **Sample data** (`performList`) uses the latest real deliveries of the event from the account's
  endpoints (`GET /api/v1/webhook-endpoints/{id}/deliveries`), so samples always match live payloads.
- **Retries:** Aurum retries a non-2xx answer with exponential backoff (1, 2, 4, … minutes, up to 8 attempts).
  An endpoint that answers `410 Gone` is disabled.

### Verifying signatures (custom receivers / Make)

Every delivery has `X-Aurum-Signature: t=<unix seconds>,v1=<hex>` where
`v1 = HMAC-SHA256(secret, "<t>.<raw body>")`. The `secret` (`whsec_…`) is returned once when the
endpoint is created (or when you rotate it with `PUT /api/v1/webhook-endpoints/{id} {"rotate_secret": true}`).
Reject timestamps older than 5 minutes. Other headers: `X-Aurum-Event` and `X-Aurum-Delivery`.

```js
const crypto = require('crypto');
function verify(secret, rawBody, header) {
  const parts = Object.fromEntries(header.split(',').map((p) => p.split('=')));
  if (Math.abs(Date.now() / 1000 - Number(parts.t)) > 300) return false;
  const expected = crypto.createHmac('sha256', secret).update(`${parts.t}.${rawBody}`).digest('hex');
  return crypto.timingSafeEqual(Buffer.from(expected), Buffer.from(parts.v1));
}
```

## Make (Integromat)

Make has no code-based app format like this one. Use the same API with Make's "Custom webhook" and
"HTTP" modules:
1. Create a Custom webhook in Make, then register its URL with `POST /api/v1/webhook-endpoints`.
2. Call actions with the HTTP module and the `Authorization: Bearer <token>` header.

## Development

```bash
cd integrations/zapier
npm install            # installs zapier-platform-core
npx zapier login
npx zapier validate
npx zapier push
```

Set the API base URL in the connection form when testing against staging (for example `https://api.staging.aurum.coach`).
