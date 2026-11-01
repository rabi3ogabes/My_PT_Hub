<?php

use App\Domain\Integrations\Models\HealthDaily;
use App\Domain\Integrations\Models\HealthSample;
use App\Domain\Integrations\Models\Integration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Fixtures\Integrations\Fixture;

beforeEach(function () {
    Http::preventStrayRequests();
    Fixture::configureProviders();
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach, ['timezone' => 'UTC']);
});

/** Sends a raw JSON body with headers to the provider webhook endpoint. */
function postWebhook($test, string $provider, string $body, array $headers = [], string $query = '')
{
    $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
    foreach ($headers as $name => $value) {
        $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
    }

    return $test->call('POST', '/webhooks/integrations/'.$provider.$query, [], [], [], $server, $body);
}

it('ingests Garmin push summaries for the matching user (URL token required)', function () {
    Fixture::integration($this->client, 'garmin', ['external_user_id' => 'garmin-user-1']);
    $body = Fixture::raw('garmin_push');

    postWebhook($this, 'garmin', $body)->assertStatus(401);
    postWebhook($this, 'garmin', $body, [], '?token=garmin-hook-token')->assertOk();

    $types = HealthSample::where('user_id', $this->client->id)->pluck('type')->all();
    expect($types)->toContain('workout', 'steps', 'active_kcal', 'distance_m', 'resting_hr', 'sleep_minutes');
    expect(HealthDaily::where('user_id', $this->client->id)->whereDate('date', '2026-10-30')->where('type', 'steps')->value('value'))->toEqual(12450.0);
    // Sleep that ends on the 30th counts toward the 30th.
    expect(HealthDaily::where('user_id', $this->client->id)->whereDate('date', '2026-10-30')->where('type', 'sleep_minutes')->value('value'))->toEqual(450.0);

    // Replaying the same push is a no-op.
    postWebhook($this, 'garmin', $body, [], '?token=garmin-hook-token')->assertOk();
    expect(HealthSample::where('user_id', $this->client->id)->count())->toBe(count($types));
});

it('verifies Fitbit subscriber endpoints and X-Fitbit-Signature', function () {
    Fixture::integration($this->client, 'fitbit', ['external_user_id' => '7ZXQ9B']);
    Http::fake([
        'api.fitbit.com/1/user/-/activities/date/2026-10-30.json' => Http::response(Fixture::json('fitbit_activities')),
    ]);

    $this->get('/webhooks/integrations/fitbit?verify=fitbit-verify-code')->assertNoContent(204);
    $this->get('/webhooks/integrations/fitbit?verify=wrong')->assertNotFound();

    $body = Fixture::raw('fitbit_webhook');
    postWebhook($this, 'fitbit', $body, ['X-Fitbit-Signature' => base64_encode(hash_hmac('sha1', $body, 'wrong&', true))])->assertStatus(401);

    $signature = base64_encode(hash_hmac('sha1', $body, 'fitbit-secret&', true));
    postWebhook($this, 'fitbit', $body, ['X-Fitbit-Signature' => $signature])->assertNoContent(204);

    expect(HealthSample::where('user_id', $this->client->id)->where('source', 'fitbit')->where('type', 'steps')->value('value'))->toEqual(9320.0)
        ->and(HealthSample::where('user_id', $this->client->id)->where('source', 'fitbit')->where('type', 'distance_m')->value('value'))->toEqual(6900.0);
    Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer fitbit-access'));
});

it('answers the Strava hub challenge and fetches the referenced activity', function () {
    Fixture::integration($this->client, 'strava', ['external_user_id' => '134815']);
    Http::fake(['www.strava.com/api/v3/activities/9876543210' => Http::response(Fixture::json('strava_activity'))]);

    $this->get('/webhooks/integrations/strava?hub.mode=subscribe&hub.challenge=15f7d1a91c1f40f8a748fd134752feb3&hub.verify_token=strava-verify')
        ->assertOk()->assertExactJson(['hub.challenge' => '15f7d1a91c1f40f8a748fd134752feb3']);
    $this->get('/webhooks/integrations/strava?hub.mode=subscribe&hub.challenge=x&hub.verify_token=nope')->assertForbidden();

    postWebhook($this, 'strava', Fixture::raw('strava_webhook'))->assertOk();

    $sample = HealthSample::where('user_id', $this->client->id)->where('source', 'strava')->firstOrFail();
    expect($sample->type)->toBe('workout')->and($sample->value)->toEqual(50.0)->and($sample->meta['kcal'])->toBe(712);
});

it('validates Oura signatures and verification challenges', function () {
    Fixture::integration($this->client, 'oura', ['external_user_id' => 'oura-user-77']);
    Http::fake(['api.ouraring.com/v2/usercollection/sleep/*' => Http::response(Fixture::json('oura_sleep'))]);

    $this->get('/webhooks/integrations/oura?verification_token=oura-verify&challenge=abc123')->assertOk()->assertExactJson(['challenge' => 'abc123']);

    $body = Fixture::raw('oura_webhook');
    $timestamp = '1793340000';
    postWebhook($this, 'oura', $body, ['x-oura-timestamp' => $timestamp, 'x-oura-signature' => str_repeat('A', 64)])->assertStatus(401);

    $signature = strtoupper(hash_hmac('sha256', $timestamp.$body, 'oura-secret'));
    postWebhook($this, 'oura', $body, ['x-oura-timestamp' => $timestamp, 'x-oura-signature' => $signature])->assertOk();

    expect(HealthSample::where('user_id', $this->client->id)->where('source', 'oura')->pluck('value', 'type')->all())
        ->toMatchArray(['sleep_minutes' => 420.0, 'hrv' => 61.0, 'resting_hr' => 49.0]);
});

it('validates WHOOP and Polar webhook signatures', function () {
    Fixture::integration($this->client, 'whoop', ['external_user_id' => '10129']);
    Fixture::integration($this->client, 'polar', ['external_user_id' => '475']);
    Http::fake([
        'api.prod.whoop.com/developer/v1/activity/workout/*' => Http::response(['id' => 'w1', 'start' => '2026-10-30T16:00:00Z', 'end' => '2026-10-30T17:00:00Z', 'sport_id' => 1, 'score' => ['strain' => 12.4, 'kilojoule' => 2092]]),
        'www.polaraccesslink.com/v3/exercises/aQlC83' => Http::response(['id' => 'aQlC83', 'start_time' => '2026-10-30T09:00:00', 'start_time_utc_offset' => 180, 'duration' => 'PT45M', 'sport' => 'RUNNING', 'calories' => 410]),
    ]);

    $whoop = Fixture::raw('whoop_webhook');
    $ts = (string) (time() * 1000);
    postWebhook($this, 'whoop', $whoop, ['X-WHOOP-Signature-Timestamp' => $ts, 'X-WHOOP-Signature' => 'bad'])->assertStatus(401);
    postWebhook($this, 'whoop', $whoop, ['X-WHOOP-Signature-Timestamp' => $ts, 'X-WHOOP-Signature' => base64_encode(hash_hmac('sha256', $ts.$whoop, 'whoop-secret', true))])->assertOk();

    $polar = Fixture::raw('polar_webhook');
    postWebhook($this, 'polar', $polar, ['Polar-Webhook-Signature' => 'bad'])->assertStatus(401);
    postWebhook($this, 'polar', $polar, ['Polar-Webhook-Signature' => hash_hmac('sha256', $polar, 'polar-secret')])->assertOk();

    $whoopSample = HealthSample::where('source', 'whoop')->firstOrFail();
    expect($whoopSample->value)->toEqual(60.0)->and($whoopSample->meta['kcal'])->toEqual(500);
    $polarSample = HealthSample::where('source', 'polar')->firstOrFail();
    expect($polarSample->start_at->toIso8601String())->toBe('2026-10-30T06:00:00+00:00')->and($polarSample->value)->toEqual(45.0);
});

it('ignores notifications for unknown users and marks deauthorized Strava athletes revoked', function () {
    $integration = Fixture::integration($this->client, 'strava', ['external_user_id' => '134815']);

    postWebhook($this, 'strava', json_encode(['object_type' => 'activity', 'aspect_type' => 'create', 'object_id' => 1, 'owner_id' => 999, 'subscription_id' => 1]))->assertOk();
    expect(HealthSample::count())->toBe(0);

    postWebhook($this, 'strava', json_encode(['object_type' => 'athlete', 'aspect_type' => 'update', 'object_id' => 134815, 'owner_id' => 134815, 'updates' => ['authorized' => 'false']]))->assertOk();
    expect($integration->refresh()->status)->toBe('revoked')->and($integration->access_token)->toBeNull();
});

it('accepts Google channel notifications only with the channel token and answers Graph validation', function () {
    $google = Fixture::integration($this->coach, 'google_calendar', ['settings' => ['channel_id' => 'chan-1', 'channel_token' => 'secret-token']]);
    Cache::forever('integrations:busy-version:'.$google->id, 3);

    $this->call('POST', '/webhooks/integrations/google_calendar', [], [], [], ['HTTP_X_GOOG_CHANNEL_ID' => 'chan-1', 'HTTP_X_GOOG_CHANNEL_TOKEN' => 'nope', 'HTTP_X_GOOG_RESOURCE_STATE' => 'exists'])->assertStatus(401);
    $this->call('POST', '/webhooks/integrations/google_calendar', [], [], [], ['HTTP_X_GOOG_CHANNEL_ID' => 'chan-1', 'HTTP_X_GOOG_CHANNEL_TOKEN' => 'secret-token', 'HTTP_X_GOOG_RESOURCE_STATE' => 'exists'])->assertStatus(202);
    expect(Cache::get('integrations:busy-version:'.$google->id))->toBe(4);

    $this->post('/webhooks/integrations/outlook?validationToken=Validation%3A+Testing')->assertOk()->assertSee('Validation: Testing');

    $outlook = Fixture::integration($this->coach, 'outlook', ['settings' => ['subscription_id' => 'sub-1', 'client_state' => 'state-xyz']]);
    postWebhook($this, 'outlook', json_encode(['value' => [['subscriptionId' => 'sub-1', 'clientState' => 'wrong']]]))->assertStatus(401);
    postWebhook($this, 'outlook', json_encode(['value' => [['subscriptionId' => 'sub-1', 'clientState' => 'state-xyz']]]))->assertStatus(202);
    expect($outlook->refresh()->last_sync_at)->not->toBeNull();
    expect(Integration::count())->toBe(2);
});
