<?php

use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Models\AiSetting;
use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Payments\Models\Payment;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\WorkoutSession;
use Tests\Feature\Ai\Support\AiFixtures;

uses(AiFixtures::class);

beforeEach(function () {
    [$this->tenant, $this->coach] = $this->proTenant();
});

it('scores churn transparently and drafts re-engagement messages', function () {
    $active = $this->clientWithProfile($this->tenant, $this->coach, ['compliance_score' => 92, 'last_active_at' => now()], ['name' => 'Omar Haddad', 'locale' => 'ar']);
    $quiet = $this->clientWithProfile($this->tenant, $this->coach, ['compliance_score' => 25, 'last_active_at' => now()->subDays(25)], ['name' => 'Sofia Ruiz', 'locale' => 'es']);
    $this->inTenant($this->tenant, function () use ($active, $quiet) {
        foreach ([1, 3, 5, 8, 10] as $d) {
            WorkoutSession::query()->create(['client_id' => $active->id, 'status' => 'completed', 'started_at' => now()->subDays($d)]);
        }
        foreach ([16, 19, 22] as $d) {
            WorkoutSession::query()->create(['client_id' => $quiet->id, 'status' => 'completed', 'started_at' => now()->subDays($d)]);
        }
        CheckinResponse::query()->create(['checkin_form_id' => CheckinForm::query()->firstOrFail()->id, 'client_id' => $quiet->id, 'status' => 'overdue', 'due_on' => now()->subDays(3)->toDateString()]);
    });

    $response = $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/ai/churn')->assertOk();

    $rows = collect($response->json('data'))->keyBy('client.id');
    expect($rows[$quiet->id]['level'])->toBe('red')
        ->and($rows[$active->id]['level'])->toBe('green')
        ->and($rows[$active->id]['suggested_message'])->toBeNull()
        ->and(collect($rows[$quiet->id]['reasons'])->pluck('factor'))->toContain('recency')->toContain('frequency')
        ->and($rows[$quiet->id]['suggested_message'])->toContain('Sofia')
        ->and($rows[$quiet->id]['suggestion_source'])->toBe('ai')
        ->and(array_sum($response->json('meta.weights')))->toEqualWithDelta(1.0, 0.0001)
        ->and(collect($rows[$quiet->id]['factors'])->pluck('factor')->all())->toBe(['recency', 'frequency', 'compliance', 'sentiment', 'payment', 'response']);
    expect(AiOutput::query()->withoutGlobalScopes()->where('feature', 'churn')->count())->toBe(1);
});

it('falls back to translated templates when AI is disabled', function () {
    $quiet = $this->clientWithProfile($this->tenant, $this->coach, ['last_active_at' => now()->subDays(30)], ['name' => 'Karim Benali', 'locale' => 'fr']);
    $this->inTenant($this->tenant, fn () => AiSetting::query()->create(['user_id' => $this->coach->id, 'disabled_features' => ['churn']]));

    $row = $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/ai/churn')->assertOk()->json('data.0');

    expect($row['suggestion_source'])->toBe('template')
        ->and($row['suggested_message'])->toBe(__('ai.churn.fallback_message', ['name' => 'Karim'], 'fr'));
});

it('answers business questions with predefined tenant-scoped tools only', function () {
    $client = $this->addClient($this->tenant, $this->coach, ['name' => 'Nora Ali']);
    $this->inTenant($this->tenant, function () use ($client) {
        CheckinResponse::query()->create(['checkin_form_id' => CheckinForm::query()->firstOrFail()->id, 'client_id' => $client->id, 'status' => 'submitted', 'submitted_at' => now()]);
        Payment::query()->create(['driver' => 'manual', 'amount_minor' => 39900, 'currency' => 'QAR', 'status' => 'succeeded', 'paid_at' => now()]);
        $program = Program::query()->create(['name' => ['en' => 'Lean 8'], 'days_per_week' => 3]);
        ProgramAssignment::query()->create(['program_id' => $program->id, 'client_id' => $client->id, 'start_date' => now()->toDateString(), 'status' => 'active']);
    });
    // Another tenant's revenue must never leak.
    [$other] = $this->proTenant();
    $this->inTenant($other, fn () => Payment::query()->create(['driver' => 'manual', 'amount_minor' => 999999, 'currency' => 'QAR', 'status' => 'succeeded', 'paid_at' => now()]));

    $this->actingAsIn($this->coach, $this->tenant);
    $this->postJson('/api/v1/ai/ask', ['question' => 'Which check-ins are pending review?'])->assertOk()
        ->assertJsonPath('data.tool', 'pending_checkins')->assertJsonPath('data.rows.0.client.name', 'Nora Ali');

    $revenue = $this->postJson('/api/v1/ai/ask', ['question' => 'What is my revenue this month?'])->assertOk();
    expect($revenue->json('data.tool'))->toBe('revenue')
        ->and($revenue->json('data.rows.0.net.amount_minor'))->toBe(39900);

    $this->postJson('/api/v1/ai/ask', ['question' => 'Which program has the best retention?'])->assertOk()
        ->assertJsonPath('data.tool', 'best_retention_program')->assertJsonPath('data.rows.0.retention_pct', 100);

    $this->postJson('/api/v1/ai/ask', ['question' => "Who hasn't paid this month?"])->assertOk()->assertJsonPath('data.tool', 'unpaid_clients');
    $this->postJson('/api/v1/ai/ask', ['question' => 'What is the weather tomorrow?'])->assertOk()->assertJsonPath('data.tool', 'unsupported');
});

it('writes marketing content in the requested language', function () {
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/content', ['type' => 'instagram_caption', 'topic' => 'برنامج رمضان', 'language' => 'ar'])
        ->assertCreated()->assertJsonPath('data.payload.language', 'ar')->assertJsonPath('data.feature', 'content');
});

it('shows and updates AI settings; only owners toggle client tools', function () {
    $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/ai/settings')->assertOk()
        ->assertJsonPath('data.tone', 'motivational')->assertJsonPath('data.client_tools.client_photo_logging', true);

    $this->putJson('/api/v1/ai/settings', [
        'tone' => 'tough_love', 'sign_off' => 'Coach Sam', 'use_emoji' => false, 'banned_words' => ['diet'],
        'sample_replies' => ['Proud of you. Keep stacking wins.'], 'disabled_features' => ['churn'], 'language' => 'fr',
        'client_tools' => ['self_guided_chat' => true],
    ])->assertOk()->assertJsonPath('data.tone', 'tough_love')->assertJsonPath('data.client_tools.self_guided_chat', true);

    expect(AiSetting::query()->withoutGlobalScopes()->where('user_id', $this->coach->id)->value('sign_off'))->toBe('Coach Sam');

    $assistant = $this->addStaff($this->tenant, 'coach');
    $this->actingAsIn($assistant, $this->tenant);
    expectApiError($this->putJson('/api/v1/ai/settings', ['client_tools' => ['client_form_check' => false]]), 403, 'forbidden');
    expectApiError($this->putJson('/api/v1/ai/settings', ['tone' => 'sarcastic']), 422, 'validation_failed');
});

it('reports usage by feature with cost', function () {
    $this->seedExercises();
    config(['ai.pricing.fake' => ['in' => 2.0, 'out' => 10.0]]);
    $this->actingAsIn($this->coach, $this->tenant);
    $id = $this->postJson('/api/v1/ai/programs', ['prompt' => '3 day full body'])->assertCreated()->json('data.id');
    $this->postJson('/api/v1/ai/content', ['type' => 'story', 'topic' => 'Leg day', 'language' => 'en'])->assertCreated();
    $this->postJson("/api/v1/ai/outputs/{$id}/approve")->assertOk();

    $usage = $this->getJson('/api/v1/ai/usage')->assertOk();
    $program = collect($usage->json('data.features'))->firstWhere('feature', 'program');

    expect($usage->json('data.totals.requests'))->toBe(2)
        ->and($usage->json('data.totals.cost_micros'))->toBeGreaterThan(0)
        ->and($usage->json('data.limits.requests_month'))->toBe(2000)
        ->and($program['calls'])->toBe(1)
        ->and($program['cost_micros'])->toBeGreaterThan(0)
        ->and($program['cost_formatted'])->toStartWith('$')
        ->and($program['acceptance_rate'])->toEqual(1.0)
        ->and($program['label'])->toBe('Program generator');
});
