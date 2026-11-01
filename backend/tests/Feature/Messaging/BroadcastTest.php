<?php

use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\Message;
use App\Domain\Messaging\Models\ScheduledMessage;
use App\Domain\Messaging\Services\BroadcastService;
use App\Domain\Tenancy\Models\ClientProfile;

beforeEach(function () {
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->a = $this->addClient($this->tenant, $this->coach, ['name' => 'Amina Benali', 'locale' => 'ar']);
    $this->b = $this->addClient($this->tenant, $this->coach, ['name' => 'Bruno Petit', 'locale' => 'fr']);
    $this->c = $this->addClient($this->tenant, $this->coach, ['name' => 'Chris Doe', 'locale' => 'en']);
    $this->inTenant($this->tenant, function () {
        ClientProfile::query()->where('user_id', $this->a->id)->update(['tags' => json_encode(['vip']), 'risk_status' => 'red']);
        ClientProfile::query()->where('user_id', $this->b->id)->update(['tags' => json_encode(['vip'])]);
    });
});

it('previews segments by tags and at-risk', function () {
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/broadcasts/preview', ['segment' => ['tags' => ['VIP']]])
        ->assertOk()->assertJsonPath('data.count', 2);
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/broadcasts/preview', ['segment' => ['tags' => ['vip'], 'at_risk' => true]])
        ->assertOk()->assertJsonPath('data.count', 1)->assertJsonPath('data.recipients.0.id', $this->a->id);
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/broadcasts/preview', ['segment' => []])
        ->assertOk()->assertJsonPath('data.count', 3);
});

it('sends a broadcast in each recipient locale, one direct conversation per recipient, exactly once', function () {
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/broadcasts', [
        'segment' => ['tags' => ['vip']],
        'body' => ['en' => 'Hi {first_name}', 'fr' => 'Salut {first_name}', 'ar' => 'مرحبا {first_name}'],
        'send_at' => now()->addHour()->toIso8601String(),
    ])->assertCreated()->assertJsonPath('data.status', 'scheduled');

    $service = app(BroadcastService::class);
    expect($service->dispatchDue())->toBe(0);
    $this->travel(61)->minutes();
    expect($service->dispatchDue())->toBe(2)->and($service->dispatchDue())->toBe(0);

    $bodies = Message::query()->get()->map(fn ($m) => $m->body)->sort()->values()->all();
    expect($bodies)->toBe(['Salut Bruno', 'مرحبا Amina']);
    expect(Conversation::withoutGlobalScopes()->where('type', 'direct')->count())->toBe(2);
    expect(ScheduledMessage::withoutGlobalScopes()->first()->recipients_count)->toBe(2);

    // Sending again reuses the existing direct conversations.
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/broadcasts', ['segment' => ['client_ids' => [$this->b->id]], 'body' => 'Again'])->assertCreated()->assertJsonPath('data.status', 'sent');
    expect(Conversation::withoutGlobalScopes()->count())->toBe(2);
});

it('cancels only unsent broadcasts and manages templates', function () {
    $id = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/broadcasts', ['body' => 'Later', 'send_at' => now()->addDay()->toIso8601String()])->json('data.id');
    $this->actingAsIn($this->coach, $this->tenant)->deleteJson("/api/v1/broadcasts/{$id}")->assertOk();
    $sent = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/broadcasts', ['body' => 'Now'])->json('data.id');
    expectApiError($this->actingAsIn($this->coach, $this->tenant)->deleteJson("/api/v1/broadcasts/{$sent}"), 409, 'already_sent');

    $template = $this->actingAsIn($this->coach, $this->tenant)->withHeader('Accept-Language', 'en')
        ->postJson('/api/v1/message-templates', ['name' => 'Check-in nudge', 'body' => ['en' => 'Hi {first_name}', 'es' => 'Hola {first_name}']])
        ->assertCreated()->assertJsonPath('data.body', 'Hi {first_name}');
    $tid = $template->json('data.id');
    $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/message-templates/{$tid}", ['body' => ['fr' => 'Salut {first_name}']])
        ->assertOk()->assertJsonPath('data.body_translations.fr', 'Salut {first_name}')->assertJsonPath('data.body_translations.es', 'Hola {first_name}');
    $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/message-templates')->assertOk()->assertJsonCount(1, 'data');
    $this->actingAsIn($this->coach, $this->tenant)->deleteJson("/api/v1/message-templates/{$tid}")->assertOk();
});
