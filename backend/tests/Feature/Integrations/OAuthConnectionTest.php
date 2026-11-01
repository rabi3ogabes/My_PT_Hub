<?php

use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Exceptions\ProviderAuthException;
use App\Domain\Integrations\Exceptions\ProviderException;
use App\Domain\Integrations\Models\HealthDaily;
use App\Domain\Integrations\Models\HealthSample;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Services\HealthIngestionService;
use App\Domain\Integrations\Services\IntegrationManager;
use App\Notifications\Integrations\IntegrationNeedsReconnect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Fixtures\Integrations\Fixture;

beforeEach(function () {
    Http::preventStrayRequests();
    Fixture::configureProviders();
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach, ['timezone' => 'UTC']);
});

it('returns an authorization URL with a signed state and completes the Strava OAuth callback', function () {
    Http::fake([
        'www.strava.com/oauth/token' => Http::response(Fixture::json('strava_token')),
        'www.strava.com/api/v3/athlete/activities*' => Http::response(Fixture::json('strava_activities')),
    ]);

    $response = $this->actingAsIn($this->client, $this->tenant)
        ->postJson('/api/v1/me/integrations/strava/connect', ['return_to' => 'aurum://integrations'])
        ->assertOk();

    $url = $response->json('data.authorization_url');
    $state = $response->json('data.state');
    expect($url)->toStartWith('https://www.strava.com/oauth/authorize?')
        ->and($url)->toContain('client_id=strava-client')
        ->and($url)->toContain('state='.rawurlencode($state))
        ->and($url)->toContain(rawurlencode(route('integrations.callback', ['provider' => 'strava'])));

    $this->get('/integrations/strava/callback?code=auth-code&state='.urlencode($state))
        ->assertRedirect('aurum://integrations?provider=strava&status=connected');

    $integration = Integration::where('user_id', $this->client->id)->where('provider', 'strava')->firstOrFail();
    expect($integration->status)->toBe('connected')
        ->and($integration->access_token)->toBe('strava-access-1')
        ->and($integration->refresh_token)->toBe('strava-refresh-1')
        ->and($integration->external_user_id)->toBe('134815')
        ->and($integration->tenant_id)->toBe($this->tenant->id);

    // Tokens are encrypted at rest.
    $raw = DB::table('integrations')->where('id', $integration->id)->value('access_token');
    expect($raw)->not->toContain('strava-access-1');

    // The backfill ran (sync queue) and imported the run.
    expect(HealthSample::where('user_id', $this->client->id)->where('source', 'strava')->where('type', 'workout')->count())->toBe(1);

    Http::assertSent(fn ($request) => $request->url() === 'https://www.strava.com/oauth/token' && $request['code'] === 'auth-code' && $request['grant_type'] === 'authorization_code');
});

it('rejects a tampered or replayed OAuth state', function () {
    Http::fake(['www.strava.com/oauth/token' => Http::response(Fixture::json('strava_token'))]);
    $state = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/integrations/strava/connect')->json('data.state');

    $this->get('/integrations/strava/callback?code=x&state='.urlencode($state.'x'))
        ->assertRedirectContains('status=error')->assertRedirectContains('reason=invalid_state');

    $this->get('/integrations/strava/callback?code=x&state='.urlencode($state))->assertRedirectContains('status=connected');
    $this->get('/integrations/strava/callback?code=x&state='.urlencode($state))->assertRedirectContains('reason=invalid_state');
});

it('uses PKCE for Garmin and keeps the verifier server-side', function () {
    Http::fake([
        'diauth.garmin.com/*' => Http::response(['access_token' => 'g-access', 'refresh_token' => 'g-refresh', 'expires_in' => 86400]),
        'apis.garmin.com/wellness-api/rest/user/id' => Http::response(['userId' => 'garmin-user-1']),
        'apis.garmin.com/wellness-api/rest/backfill/*' => Http::response(null, 202),
    ]);

    $data = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/integrations/garmin/connect')->assertOk()->json('data');
    parse_str((string) parse_url($data['authorization_url'], PHP_URL_QUERY), $query);
    expect($query['code_challenge_method'])->toBe('S256')->and($query['code_challenge'])->not->toBeEmpty();

    $this->get('/integrations/garmin/callback?code=c&state='.urlencode($data['state']))->assertRedirectContains('status=connected');

    Http::assertSent(function ($request) use ($query) {
        if ($request->url() !== 'https://diauth.garmin.com/di-oauth2-service/oauth/token') {
            return false;
        }
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $request['code_verifier'], true)), '+/', '-_'), '=');

        return $challenge === $query['code_challenge'];
    });
    expect(Integration::where('provider', 'garmin')->value('external_user_id'))->toBe('garmin-user-1');
});

it('refreshes an expiring token before pulling', function () {
    Http::fake([
        'www.strava.com/oauth/token' => Http::response(Fixture::json('strava_refresh')),
        'www.strava.com/api/v3/athlete/activities*' => Http::response([]),
    ]);
    $integration = Fixture::integration($this->client, 'strava', ['token_expires_at' => now()->addMinute()]);

    app(IntegrationManager::class)->syncHealth($integration);

    $integration->refresh();
    expect($integration->access_token)->toBe('strava-access-2')
        ->and($integration->refresh_token)->toBe('strava-refresh-2')
        ->and($integration->token_expires_at->isFuture())->toBeTrue()
        ->and($integration->last_sync_at)->not->toBeNull();
    Http::assertSent(fn ($r) => $r->url() === 'https://www.strava.com/oauth/token' && $r['grant_type'] === 'refresh_token' && $r['refresh_token'] === 'strava-refresh');
    Http::assertSent(fn ($r) => str_starts_with($r->url(), 'https://www.strava.com/api/v3/athlete/activities') && $r->hasHeader('Authorization', 'Bearer strava-access-2'));
});

it('flags the integration and notifies the user when the refresh token is revoked', function () {
    Notification::fake();
    Http::fake(['www.strava.com/oauth/token' => Http::response(['message' => 'Bad Request', 'errors' => [['code' => 'invalid']]], 400)]);
    $integration = Fixture::integration($this->client, 'strava', ['token_expires_at' => now()->subMinute()]);

    expect(fn () => app(IntegrationManager::class)->syncHealth($integration))->toThrow(ProviderAuthException::class);

    expect($integration->refresh()->status)->toBe('error');
    Notification::assertSentTo($this->client, IntegrationNeedsReconnect::class, fn ($n) => $n->provider === 'strava');

    $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/integrations')
        ->assertOk()
        ->assertJsonFragment(['provider' => 'strava', 'status' => 'error', 'needs_reconnect' => true]);
});

it('backs off after transient failures', function () {
    Http::fake(['www.strava.com/api/v3/athlete/activities*' => Http::response('down', 503)]);
    $integration = Fixture::integration($this->client, 'strava');

    try {
        app(IntegrationManager::class)->syncHealth($integration);
    } catch (ProviderException) {
    }

    $integration->refresh();
    expect($integration->status)->toBe('connected')
        ->and($integration->failure_count)->toBe(1)
        ->and($integration->next_attempt_at->between(now()->addMinutes(4), now()->addMinutes(6)))->toBeTrue();
});

it('restricts calendar integrations to staff', function () {
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/integrations/google_calendar/connect'), 403, 'forbidden');

    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/me/integrations/google_calendar/connect')
        ->assertOk()->assertJsonPath('data.provider', 'google_calendar');
});

it('lists the catalogue for staff with client adoption and for clients with their sources', function () {
    Fixture::integration($this->client, 'garmin');

    $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/integrations')
        ->assertOk()
        ->assertJsonPath('meta.clients_connected.garmin', 1)
        ->assertJsonFragment(['provider' => 'google_calendar', 'connected' => false]);

    $mine = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/integrations')->assertOk()->json('data');
    $providers = array_column($mine, 'provider');
    expect($providers)->toContain('garmin', 'apple_health')->not->toContain('google_calendar');
    expect(collect($mine)->firstWhere('provider', 'garmin')['connected'])->toBeTrue();
});

it('disconnects, revokes at the provider and deletes the source data on request', function () {
    Http::fake(['www.strava.com/oauth/deauthorize' => Http::response([])]);
    Fixture::integration($this->client, 'strava');
    app(HealthIngestionService::class)->ingest($this->client, [
        SampleData::make('workout', 50, 'min', '2026-10-30T05:00:00Z', '2026-10-30T05:52:00Z', 'strava', 'act-1'),
    ]);
    expect(HealthDaily::where('user_id', $this->client->id)->where('type', 'workout')->count())->toBe(1);

    $this->actingAsIn($this->client, $this->tenant)->deleteJson('/api/v1/me/integrations/strava', ['delete_data' => true])
        ->assertOk()->assertJsonPath('data.deleted_samples', 1);

    expect(Integration::where('user_id', $this->client->id)->where('provider', 'strava')->exists())->toBeFalse()
        ->and(HealthSample::where('user_id', $this->client->id)->count())->toBe(0)
        ->and(HealthDaily::where('user_id', $this->client->id)->count())->toBe(0);
    Http::assertSent(fn ($r) => $r->url() === 'https://www.strava.com/oauth/deauthorize');
});

it('queues a manual sync once per minute', function () {
    Http::fake(['www.strava.com/api/v3/athlete/activities*' => Http::response([])]);
    Fixture::integration($this->client, 'strava');

    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/integrations/strava/sync')->assertStatus(202)->assertJsonPath('data.queued', true);
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/integrations/strava/sync'), 429, 'too_many_requests');
});
