<?php

use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Services\WhatsAppService;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\Message;
use App\Notifications\Integrations\IntegrationNeedsReconnect;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\Fixtures\Integrations\Fixture;

beforeEach(function () {
    Http::preventStrayRequests();
    Fixture::configureProviders();
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach, ['name' => 'Omar Khalid', 'locale' => 'ar']);
    $this->whatsapp = app(WhatsAppService::class);
});

function signedInbound($test, string $body, string $secret = 'wa-app-secret')
{
    return $test->call('POST', '/webhooks/integrations/whatsapp', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => 'sha256='.hash_hmac('sha256', $body, $secret),
    ], $body);
}

it('renders every template in Arabic, French, English and Spanish', function () {
    $params = ['name' => 'Omar', 'session' => 'PT session', 'time' => '18:00'];

    expect($this->whatsapp->renderTemplate('session_reminder', $params, 'en'))->toBe('Hi Omar, a reminder that your PT session is at 18:00. See you there!')
        ->and($this->whatsapp->renderTemplate('session_reminder', $params, 'fr'))->toBe('Bonjour Omar, petit rappel : votre PT session a lieu à 18:00. À tout à l’heure !')
        ->and($this->whatsapp->renderTemplate('session_reminder', $params, 'es'))->toBe('Hola Omar, te recordamos que tu PT session es a las 18:00. ¡Nos vemos!')
        ->and($this->whatsapp->renderTemplate('session_reminder', $params, 'ar'))->toBe('مرحبًا Omar، نذكّرك بأن موعد PT session في 18:00. نراك هناك!');

    foreach (['ar', 'fr', 'en', 'es'] as $locale) {
        foreach (array_keys(config('integrations.whatsapp.templates')) as $template) {
            $text = $this->whatsapp->renderTemplate($template, ['name' => 'N', 'session' => 'S', 'time' => 'T', 'amount' => 'A', 'link' => 'L', 'coach' => 'C', 'provider' => 'P'], $locale);
            expect($text)->not->toContain('integrations.')->not->toContain(':name');
        }
        $payload = $this->whatsapp->templatePayload('97455501234', 'payment_link', ['link' => 'https://pay.example/x', 'name' => 'Omar', 'amount' => 'QAR 399.00'], $locale);
        expect($payload['template']['language']['code'])->toBe($locale)
            ->and(array_column($payload['template']['components'][0]['parameters'], 'text'))->toBe(['Omar', 'QAR 399.00', 'https://pay.example/x']);
    }
});

it('sends templates only to opted-in users in their language', function () {
    Http::fake(['graph.facebook.com/*' => Http::response(Fixture::json('whatsapp_send'))]);

    expect($this->whatsapp->sendTemplate($this->client, 'checkin_nudge', ['name' => 'Omar', 'coach' => 'Sara']))->toBeNull();
    Http::assertNothingSent();

    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/integrations/whatsapp/connect', ['phone' => '+974 5550 1234'])
        ->assertStatus(422);
    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/integrations/whatsapp/connect', ['phone' => '+974 5550 1234', 'consent' => true])
        ->assertOk()->assertJsonPath('data.connected', true)->assertJsonPath('data.account', '+97455501234');

    $id = $this->whatsapp->sendTemplate($this->client, 'checkin_nudge', ['name' => 'Omar', 'coach' => 'Sara']);

    expect($id)->toStartWith('wamid.');
    Http::assertSent(fn (HttpRequest $r) => $r->url() === 'https://graph.facebook.com/v21.0/106540352242922/messages'
        && $r->hasHeader('Authorization', 'Bearer wa-token')
        && $r['to'] === '97455501234'
        && $r['template']['name'] === 'checkin_nudge'
        && $r['template']['language']['code'] === 'ar');

    $this->actingAsIn($this->client, $this->tenant)->deleteJson('/api/v1/me/integrations/whatsapp')->assertOk();
    expect($this->whatsapp->isOptedIn($this->client))->toBeFalse();
});

it('delivers notifications through the WhatsApp channel', function () {
    Http::fake(['graph.facebook.com/*' => Http::response(Fixture::json('whatsapp_send'))]);
    $this->whatsapp->optIn($this->client, '+97455501234', $this->tenant);

    $this->client->notify(new IntegrationNeedsReconnect('garmin'));

    Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'graph.facebook.com')
        && $r['template']['name'] === 'integration_reconnect'
        && $r['template']['language']['code'] === 'ar'
        && $r['template']['components'][0]['parameters'][1]['text'] === 'Garmin');
});

it('verifies the webhook subscription challenge', function () {
    $this->get('/webhooks/integrations/whatsapp?hub.mode=subscribe&hub.verify_token=wa-verify&hub.challenge=1158201444')
        ->assertOk()->assertSeeText('1158201444');
    $this->get('/webhooks/integrations/whatsapp?hub.mode=subscribe&hub.verify_token=bad&hub.challenge=1')->assertForbidden();
});

it('routes signed inbound replies into the client\'s direct conversation tagged whatsapp', function () {
    $this->whatsapp->optIn($this->client, '+974 5550 1234', $this->tenant);
    $body = Fixture::raw('whatsapp_inbound');

    signedInbound($this, $body, 'wrong-secret')->assertStatus(401);
    expect(Message::count())->toBe(0);

    signedInbound($this, $body)->assertOk();
    signedInbound($this, $body)->assertOk(); // provider retry is idempotent

    $message = Message::sole();
    expect($message->channel)->toBe('whatsapp')
        ->and($message->sender_id)->toBe($this->client->id)
        ->and($message->body)->toBe("Hi coach, I finished today's workout!")
        ->and($message->meta['whatsapp_id'])->toStartWith('wamid.');

    $conversation = Conversation::withoutGlobalScopes()->findOrFail($message->conversation_id);
    expect($conversation->tenant_id)->toBe($this->tenant->id)
        ->and($conversation->type)->toBe('direct')
        ->and($conversation->participants()->pluck('user_id')->sort()->values()->all())->toBe(collect([$this->coach->id, $this->client->id])->sort()->values()->all());
});

it('withdraws consent on STOP and drops messages from unknown numbers', function () {
    $this->whatsapp->optIn($this->client, '97455501234', $this->tenant);

    $stop = str_replace("Hi coach, I finished today's workout!", 'STOP', Fixture::raw('whatsapp_inbound'));
    signedInbound($this, $stop)->assertOk();
    expect($this->whatsapp->isOptedIn($this->client))->toBeFalse()
        ->and(Integration::where('provider', 'whatsapp')->value('status'))->toBe('revoked');

    $unknown = str_replace('97455501234', '33600000000', Fixture::raw('whatsapp_inbound'));
    signedInbound($this, $unknown)->assertOk();
    expect(Message::count())->toBe(1);
});
