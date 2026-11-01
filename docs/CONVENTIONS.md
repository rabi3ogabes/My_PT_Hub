# Engineering Conventions (read before writing code)

This is the contract every contributor (human or agent) follows. The root `CLAUDE.md` holds product rules; this file holds code rules.

## Repository
```
backend/            Laravel 13 (PHP 8.3), Livewire 4, Filament 5, Pest 4, Tailwind 4
apps/client         Flutter client app
apps/coach          Flutter coach app
packages/core       Shared Dart package
packages/design_tokens  tokens.json + generate.mjs (Tailwind CSS + Dart outputs)
docs/               specs, API contract, ADRs
infra/              docker, CI, fastlane
```

## Backend layout
- Domain code: `app/Domain/{Module}/{Models,Services,Actions,Events,Listeners,Jobs,Gateways,Data}`.
  Modules: Identity, Tenancy, Billing, Workouts, Nutrition, Habits, Checkins, Progress, Messaging, Community, Calendar, Payments, Crm, Ai, Integrations, Marketing, Shared.
- HTTP API: `app/Http/Api/V1/{Module}/*Controller.php`, resources in `app/Http/Api/V1/Resources/`, form requests in `app/Http/Api/V1/Requests/{Module}/`.
- Web (Livewire/Blade): `app/Livewire/{Area}/...`, `resources/views/...`, routes in `routes/web/{area}.php` (auto-loaded).
- Scheduled tasks: `routes/schedule/{module}.php` (auto-loaded by `routes/console.php`).
- Seeders: `database/seeders/Modules/{Name}Seeder.php` (auto-discovered, alphabetical). Demo data only in `DemoSeeder`.
- New tables/columns: add a NEW migration file `database/migrations/2026_02_xx_xxxxxx_{desc}.php`. Never edit existing migrations.
- **All models already exist** in `app/Domain/*/Models` (99 models, generated from the schema). Read them before writing queries. Add relations/helpers to them as needed.

## IDs, money, units, time
- Every PK is a ULID string (`HasUlids` via `BaseModel`). Offline-created rows (workout sessions, session sets, food logs, messages) accept a client-generated ULID `id`; writes must be idempotent upserts on that id.
- Money: integer minor units + ISO currency (`App\Domain\Shared\Support\Money`). Never floats for money.
- Units: store metric (kg, cm, ml, m). Convert only for display (`Units`).
- Time: store UTC; render in user/tenant timezone. Dates (log_date, measured_on) are the user's local calendar date.

## Multi-tenancy (critical)
- `App\Domain\Shared\Tenancy\TenantContext` (singleton) holds the active tenant. HTTP: set by `tenant` middleware (`ResolveTenant`) from `X-Tenant` header / subdomain / user's current tenant; it runs before route-model binding.
- Tenant-owned models use `BelongsToTenant` (global scope + auto `tenant_id` on create). Library models (exercises, foods, recipes, habits, workouts, programs) use `SharedWithTenant` (global rows `tenant_id = null` + tenant's own rows).
- Child tables without `tenant_id` (e.g. `session_sets`, `messages`) are always reached through their tenant-scoped parent — never query them by id alone from user input without checking the parent.
- Jobs/commands: wrap per-tenant work in `app(TenantContext::class)->run($tenant, fn () => ...)`.
- Public (unauthenticated) endpoints that show tenant data must set the context explicitly from the resolved coach/tenant, or use `withoutGlobalScopes()` with explicit `where('tenant_id', ...)`.
- Every module must include at least one cross-tenant isolation test.

## Authorization
- Roles per tenant: owner, coach, assistant, client (pivot `tenant_user.role` + spatie team role). Route groups already apply `role:owner,coach,assistant` for staff endpoints.
- Clients may only read/write **their own** data (`client_id = auth()->id()`). Staff may access any client of the tenant. Assistants: check `$user->can('permission')` (see `App\Domain\Tenancy\Services\Roles`) for manage actions.
- Helpers on `User`: `roleIn()`, `isStaffIn()`, `isOwnerIn()`, `isClientIn()`, `membershipFor()`.
- Plan gates: `App\Domain\Billing\Services\PlanGate` → `feature($tenant, 'ai_copilot')`, `assertFeature()`, `assertCanAddClient()`.

## API conventions
- Extend `App\Http\Api\ApiController`; respond with `$this->ok($data, $meta)`, `$this->created()`, `$this->paginated($paginator, Resource::class)`.
- Envelope: `{"data":..., "meta":{}, "links":{}, "errors":[]}`. Errors: throw `App\Domain\Shared\Support\ApiException` (`code`, `message`, `status`, `field_errors`) — rendered automatically. Validation errors render as `validation_failed` with `field_errors`.
- Lists: cursor pagination (`->cursorPaginate($this->perPage())`) for feeds/history; length-aware for admin tables. Filtering `filter[status]=`, sorting `sort=-created_at`, search `q=`.
- Request validation inline (`$request->validate`) or FormRequest; only validated data reaches models.
- Translatable JSON columns (`name`, `description`, ...): models use `HasTranslations`; resources output `name` (localized via `translate('name')`) and `name_translations` (raw object). Accept either a string (stored under current locale) or an object `{en, ar, fr, es}` on write.
- Dates in responses: ISO 8601 (`toIso8601String()`); dates without time as `Y-m-d`.
- Keep controllers thin; business rules live in `app/Domain/*/Services`.

## Notifications & events
- Extend `App\Notifications\AppNotification` (channels by user prefs: database, mail, FCM push; quiet hours) or use `GenericNotification` for simple messages.
- Domain events in `app/Domain/{Module}/Events`, listeners registered via `Event::listen` in the module's service provider or `AppServiceProvider` (Laravel auto-discovery is on for `app/Listeners` only — prefer explicit `Event::listen` in a module provider `app/Providers/{Module}ServiceProvider.php` added to `bootstrap/providers.php`).
- Broadcasting (Reverb): channels in `routes/channels.php` — `private-tenant.{id}`, `private-user.{id}`, `presence-conversation.{id}`.

## i18n
- Locales: `en`, `ar` (RTL), `fr`, `es`. **No hard-coded user-facing strings.** Use `__('group.key')`.
- Each module owns its lang group file(s) in all four locales: `lang/{en,ar,fr,es}/{group}.php`. Do not edit another module's group file. Arabic must be natural Modern Standard Arabic; French and Spanish native-quality.

## Testing
- Pest. Feature tests in `tests/Feature/{Module}/`, unit tests in `tests/Unit/{Module}/`.
- `tests/TestCase.php` helpers: `createCoachTenant()`, `addClient($tenant, $coach)`, `addStaff($tenant, 'coach')`, `actingAsIn($user, $tenant)`, `inTenant($tenant, fn)`; global `expectApiError($response, 403, 'forbidden')`.
- Run only your module: `php artisan test tests/Feature/{Module}`; the full suite must stay green.
- External HTTP is always faked (`Http::fake`); AI uses the Fake provider.

## Style
- `declare` nothing exotic; PSR-12 via Pint (`vendor/bin/pint`). Typed properties/returns everywhere. No TODOs, no placeholder data in production code paths.
