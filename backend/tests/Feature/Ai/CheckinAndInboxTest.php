<?php

use App\Domain\Ai\Models\AiSetting;
use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\ConversationParticipant;
use App\Domain\Messaging\Models\Message;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Workouts\Models\WorkoutSession;
use Tests\Feature\Ai\Support\AiFixtures;

uses(AiFixtures::class);

beforeEach(function () {
    [$this->tenant, $this->coach] = $this->proTenant();
    $this->client = $this->clientWithProfile($this->tenant, $this->coach, ['goal' => 'fat_loss'], ['name' => 'Léa Martin', 'locale' => 'fr']);
});

function submittedCheckin($test, array $answers): CheckinResponse
{
    return app(\App\Domain\Shared\Tenancy\TenantContext::class)->run($test->tenant, function () use ($test, $answers) {
        $form = CheckinForm::query()->firstOrFail();
        foreach ([1, 4, 8] as $daysAgo) {
            WorkoutSession::query()->create(['client_id' => $test->client->id, 'status' => 'completed', 'started_at' => now()->subDays($daysAgo), 'name' => 'Session']);
        }
        BodyMetric::query()->create(['client_id' => $test->client->id, 'measured_on' => now()->subDays(14)->toDateString(), 'weight_kg' => 72.4]);
        BodyMetric::query()->create(['client_id' => $test->client->id, 'measured_on' => now()->toDateString(), 'weight_kg' => 71.6]);

        return CheckinResponse::query()->create([
            'checkin_form_id' => $form->id, 'client_id' => $test->client->id, 'status' => 'submitted', 'answers' => $answers, 'submitted_at' => now(),
        ]);
    });
}

it('summarises a check-in with numbers and drafts a reply in the client language and coach voice', function () {
    $this->inTenant($this->tenant, fn () => AiSetting::query()->create(['user_id' => $this->coach->id, 'tone' => 'nurturing', 'sign_off' => '— Coach Sara', 'use_emoji' => false]));
    $response = submittedCheckin($this, ['energy' => 7, 'sleep' => 6, 'wins' => 'Trois séances faites !', 'struggles' => 'Difficile de suivre les repas']);

    $result = $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/ai/checkins/{$response->id}/summary")->assertCreated();

    $payload = $result->json('data.payload');
    expect($payload['language'])->toBe('fr')
        ->and($payload['reply_draft'])->toContain('Léa')->toEndWith('— Coach Sara')
        ->and($payload['metrics']['sessions_last_14_days'])->toBe(3)
        ->and($payload['metrics']['weight_change_kg'])->toBe(-0.8)
        ->and($payload['wins'])->not->toBeEmpty()
        ->and($result->json('data.grounding.checkins'))->toBe(1)
        ->and($result->json('data.grounding.sessions'))->toBe(3)
        ->and($result->json('data.grounding.label'))->toBe('Based on 1 check-ins and 3 sessions')
        ->and($result->json('data.status'))->toBe('draft');

    $prompt = $this->fake()->callsFor('checkin_summary')[0]['prompt'];
    expect($prompt->messages[0]['content'])->toContain('nurturing')->not->toContain($this->client->email)->not->toContain('Martin');
});

it('adds safety risk flags from the answers and retries when a banned word is used', function () {
    $this->inTenant($this->tenant, fn () => AiSetting::query()->create(['user_id' => $this->coach->id, 'banned_words' => ['diet']]));
    $response = submittedCheckin($this, ['struggles' => "J'ai mal au genou depuis mardi"]);
    $this->fake()->push('checkin_summary', fn ($p) => array_merge(
        app(\App\Domain\Ai\Providers\Fake\FakeResponder::class)->structured('checkin_summary', $p->context),
        ['reply_draft' => 'Stick to the diet!'],
    ));

    $result = $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/ai/checkins/{$response->id}/summary")->assertCreated();

    expect(collect($result->json('data.payload.risk_flags'))->pluck('type'))->toContain('injury')
        ->and(collect($result->json('data.flags'))->pluck('type'))->toContain('injury')
        ->and($result->json('data.payload.reply_draft'))->not->toContain('diet')
        ->and(\App\Domain\Ai\Models\AiRequest::query()->pluck('status')->all())->toBe(['invalid', 'ok']);
});

it('rejects pending check-ins and other tenants check-ins', function () {
    $pending = $this->inTenant($this->tenant, fn () => CheckinResponse::query()->create(['checkin_form_id' => CheckinForm::query()->firstOrFail()->id, 'client_id' => $this->client->id, 'status' => 'pending']));
    $this->actingAsIn($this->coach, $this->tenant);
    expectApiError($this->postJson("/api/v1/ai/checkins/{$pending->id}/summary"), 422, 'checkin_not_submitted');

    [$other, $otherOwner] = $this->proTenant();
    $this->actingAsIn($otherOwner, $other);
    expectApiError($this->postJson("/api/v1/ai/checkins/{$pending->id}/summary"), 404, 'not_found');
});

function conversationWith($test, array $messages): Conversation
{
    return app(\App\Domain\Shared\Tenancy\TenantContext::class)->run($test->tenant, function () use ($test, $messages) {
        $c = Conversation::query()->create(['type' => 'direct', 'created_by' => $test->coach->id]);
        foreach ([$test->coach, $test->client] as $u) {
            ConversationParticipant::query()->create(['conversation_id' => $c->id, 'user_id' => $u->id]);
        }
        foreach ($messages as $i => [$who, $body]) {
            $m = Message::query()->create(['conversation_id' => $c->id, 'sender_id' => $who === 'client' ? $test->client->id : $test->coach->id, 'type' => 'text', 'body' => $body]);
            $m->forceFill(['created_at' => now()->subMinutes(30 - $i)])->save();
        }

        return $c;
    });
}

it('drafts an inbox reply and detects urgent pain and cancellation intent', function () {
    $conversation = conversationWith($this, [
        ['coach', 'How did leg day go?'],
        ['client', "J'ai une douleur au genou et je pense annuler mon abonnement. Mon email: lea@example.com"],
    ]);

    $result = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/reply-draft', ['conversation_id' => $conversation->id])->assertCreated();

    expect($result->json('data.payload.priority'))->toBe('urgent')
        ->and($result->json('data.payload.priority_reasons'))->toContain('pain_or_injury')->toContain('cancellation_intent')
        ->and($result->json('data.payload.language'))->toBe('fr')
        ->and($result->json('data.payload.reply'))->toContain('Léa')
        ->and($result->json('data.status'))->toBe('draft');

    $prompt = $this->fake()->callsFor('reply_draft')[0]['prompt'];
    expect($prompt->messages[0]['content'])->not->toContain('lea@example.com')->toContain('[email]');
    expect(Message::query()->where('conversation_id', $conversation->id)->count())->toBe(2); // nothing sent
});

it('summarises a thread and refuses another tenant conversation', function () {
    $conversation = conversationWith($this, [['client', 'Can we move Thursday to Friday?'], ['coach', 'Sure, 7pm works.']]);

    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/ai/summarize/{$conversation->id}")
        ->assertCreated()->assertJsonPath('data.feature', 'thread_summary')->assertJsonPath('data.payload.priority', 'normal');

    [$other, $otherOwner] = $this->proTenant();
    $this->actingAsIn($otherOwner, $other);
    expectApiError($this->postJson("/api/v1/ai/summarize/{$conversation->id}"), 404, 'not_found');
    expectApiError($this->postJson('/api/v1/ai/reply-draft', ['conversation_id' => $conversation->id]), 404, 'not_found');
});
