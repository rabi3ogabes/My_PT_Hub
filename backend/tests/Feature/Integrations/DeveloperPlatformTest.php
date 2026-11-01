<?php

use App\Domain\Integrations\Models\WebhookDelivery;
use App\Domain\Integrations\Models\WebhookEndpoint;
use App\Domain\Integrations\Services\OutgoingWebhooks;
use App\Domain\Tenancy\Events\ClientJoined;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
    [$this->tenant, $this->owner] = $this->createCoachTenant();
});

function makeEndpoint($test, array $events = ['client.created'], string $url = 'https://hooks.example.com/aurum'): WebhookEndpoint
{
    return (fn () => $this->inTenant($this->tenant, fn () => WebhookEndpoint::create(['url' => $url, 'events' => $events, 'secret' => 'whsec_test', 'is_active' => true])))->call($test);
}

it('creates endpoints and shows the signing secret only once', function () {
    $created = $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/webhook-endpoints', [
        'url' => 'https://hooks.zapier.com/hooks/standard/1/abc',
        'events' => ['client.created', 'workout.completed'],
        'description' => 'Zapier',
    ])->assertCreated();

    expect($created->json('data.secret'))->toStartWith('whsec_');
    $id = $created->json('data.id');

    $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/webhook-endpoints')
        ->assertOk()->assertJsonPath('data.0.id', $id)->assertJsonMissingPath('data.0.secret');

    $rotated = $this->actingAsIn($this->owner, $this->tenant)->putJson("/api/v1/webhook-endpoints/{$id}", ['rotate_secret' => true, 'events' => ['*']])
        ->assertOk()->assertJsonPath('data.events', ['*']);
    expect($rotated->json('data.secret'))->not->toBe($created->json('data.secret'));

    $this->actingAsIn($this->owner, $this->tenant)->deleteJson("/api/v1/webhook-endpoints/{$id}")->assertOk();
    expect(WebhookEndpoint::withoutGlobalScopes()->count())->toBe(0);
});

it('rejects non-HTTPS or private webhook URLs and unknown events', function () {
    foreach ([['url' => 'http://example.com/x'], ['url' => 'https://127.0.0.1/x'], ['url' => 'https://localhost/x'], ['events' => ['user.hacked']]] as $override) {
        $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/webhook-endpoints', array_merge(['url' => 'https://example.com/hook', 'events' => ['client.created']], $override))
            ->assertStatus(422);
    }
});

it('signs deliveries with X-Aurum-Signature (t=…,v1=…)', function () {
    Http::fake(['hooks.example.com/*' => Http::response(['ok' => true])]);
    makeEndpoint($this, ['lead.created']);
    makeEndpoint($this, ['client.created'], 'https://hooks.example.com/other');

    $count = app(OutgoingWebhooks::class)->dispatch($this->tenant, 'lead.created', ['lead' => ['id' => 'L1', 'name' => 'Noura']]);

    expect($count)->toBe(1);
    Http::assertSentCount(1);
    Http::assertSent(function (HttpRequest $request) {
        $header = $request->header('X-Aurum-Signature')[0];
        expect($header)->toMatch('/^t=\d+,v1=[a-f0-9]{64}$/');

        return OutgoingWebhooks::verify('whsec_test', $request->body(), $header)
            && ! OutgoingWebhooks::verify('whsec_other', $request->body(), $header)
            && $request->header('X-Aurum-Event')[0] === 'lead.created'
            && $request['data']['lead']['name'] === 'Noura';
    });

    $delivery = WebhookDelivery::firstOrFail();
    expect($delivery->status)->toBe('delivered')->and($delivery->attempts)->toBe(1)->and($delivery->response_code)->toBe(200);
});

it('retries failed deliveries with exponential backoff and gives up after max attempts', function () {
    Http::fake(['hooks.example.com/*' => Http::sequence()->push('err', 500)->push('err', 502)->push(['ok' => true], 200)]);
    makeEndpoint($this, ['client.created']);

    app(OutgoingWebhooks::class)->dispatch($this->tenant, 'client.created', ['client' => ['id' => 'c1']]);

    $delivery = WebhookDelivery::firstOrFail();
    expect($delivery->status)->toBe('delivered')->and($delivery->attempts)->toBe(3);
    Http::assertSentCount(3);
    expect(OutgoingWebhooks::backoffSeconds(1))->toBe(60)
        ->and(OutgoingWebhooks::backoffSeconds(4))->toBe(480)
        ->and(OutgoingWebhooks::backoffSeconds(20))->toBe(21600);

    config(['integrations.webhooks.max_attempts' => 2]);
    Http::fake(['hooks.example.com/*' => Http::response('nope', 500)]);
    app(OutgoingWebhooks::class)->dispatch($this->tenant, 'client.created', ['client' => ['id' => 'c2']]);
    $failed = WebhookDelivery::where('status', 'failed')->firstOrFail();
    expect($failed->attempts)->toBe(2)->and($failed->error)->toBe('http_500')->and($failed->next_retry_at)->toBeNull();
});

it('disables an endpoint that answers 410 Gone', function () {
    Http::fake(['hooks.example.com/*' => Http::response('', 410)]);
    $endpoint = makeEndpoint($this, ['*']);

    app(OutgoingWebhooks::class)->dispatch($this->tenant, 'workout.completed', []);

    expect($endpoint->refresh()->is_active)->toBeFalse()->and(WebhookDelivery::first()->status)->toBe('failed');
});

it('emits client.created when a client joins and logs deliveries', function () {
    Http::fake(['hooks.example.com/*' => Http::response('ok')]);
    $endpoint = makeEndpoint($this, ['client.created']);
    $client = $this->addClient($this->tenant, $this->owner);

    event(new ClientJoined($this->tenant, $client));

    Http::assertSent(fn (HttpRequest $r) => $r['event'] === 'client.created' && $r['data']['client']['email'] === $client->email && $r['tenant_id'] === $this->tenant->id);

    $this->actingAsIn($this->owner, $this->tenant)->getJson("/api/v1/webhook-endpoints/{$endpoint->id}/deliveries")
        ->assertOk()->assertJsonPath('data.0.event', 'client.created')->assertJsonPath('data.0.status', 'delivered')
        ->assertJsonStructure(['meta' => ['next_cursor']]);
});

it('isolates webhook endpoints between tenants and limits management to owners/coaches', function () {
    $endpoint = makeEndpoint($this);
    [$other, $otherOwner] = $this->createCoachTenant();

    expectApiError($this->actingAsIn($otherOwner, $other)->getJson("/api/v1/webhook-endpoints/{$endpoint->id}/deliveries"), 404, 'not_found');
    expectApiError($this->actingAsIn($otherOwner, $other)->deleteJson("/api/v1/webhook-endpoints/{$endpoint->id}"), 404, 'not_found');
    $this->actingAsIn($otherOwner, $other)->getJson('/api/v1/webhook-endpoints')->assertOk()->assertJsonCount(0, 'data');

    Http::fake();
    app(OutgoingWebhooks::class)->dispatch($other, 'client.created', []);
    Http::assertNothingSent();

    $assistant = $this->addStaff($this->tenant, 'assistant');
    expectApiError($this->actingAsIn($assistant, $this->tenant)->postJson('/api/v1/webhook-endpoints', ['url' => 'https://example.com/h', 'events' => ['client.created']]), 403, 'forbidden');
});

it('issues personal access tokens with scopes shown once and enforces them', function () {
    $created = $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/api-tokens', [
        'name' => 'Zapier', 'scopes' => ['webhooks:manage', 'clients:read'], 'expires_in_days' => 90,
    ])->assertCreated();
    $plain = $created->json('data.token');
    expect($plain)->toContain('|')->and($created->json('data.scopes'))->toBe(['webhooks:manage', 'clients:read']);

    $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/api-tokens')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonMissingPath('data.0.token');

    app('auth')->forgetGuards();
    $this->withHeaders(['Authorization' => 'Bearer '.$plain])->getJson('/api/v1/webhook-endpoints')->assertOk();
    app('auth')->forgetGuards();
    $this->withHeaders(['Authorization' => 'Bearer '.$plain])->postJson('/api/v1/webhook-endpoints', ['url' => 'https://hooks.zapier.com/x', 'events' => ['client.created']])->assertCreated();
    app('auth')->forgetGuards();
    expectApiError($this->withHeaders(['Authorization' => 'Bearer '.$plain])->postJson('/api/v1/leads', ['name' => 'X']), 403, 'forbidden');
    app('auth')->forgetGuards();
    expectApiError($this->withHeaders(['Authorization' => 'Bearer '.$plain])->getJson('/api/v1/api-tokens'), 403, 'forbidden');

    [$other] = $this->createCoachTenant();
    app('auth')->forgetGuards();
    expectApiError($this->withHeaders(['Authorization' => 'Bearer '.$plain, 'X-Tenant' => $other->id])->getJson('/api/v1/webhook-endpoints'), 403, 'forbidden');

    $id = $created->json('data.id');
    app('auth')->forgetGuards();
    $this->actingAsIn($this->owner, $this->tenant)->deleteJson("/api/v1/api-tokens/{$id}")->assertOk();
    expect($this->owner->tokens()->count())->toBe(0);
});

it('verifies the receiver-side signature helper rejects stale timestamps', function () {
    $body = '{"event":"x"}';
    $old = OutgoingWebhooks::sign('s', $body, time() - 3600);
    expect(OutgoingWebhooks::verify('s', $body, $old))->toBeFalse()
        ->and(OutgoingWebhooks::verify('s', $body, OutgoingWebhooks::sign('s', $body, time())))->toBeTrue();
});
