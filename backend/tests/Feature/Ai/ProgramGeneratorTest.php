<?php

use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Models\AiRequest;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutExercise;
use Tests\Feature\Ai\Support\AiFixtures;

uses(AiFixtures::class);

beforeEach(function () {
    $this->exercises = $this->seedExercises();
    [$this->tenant, $this->coach] = $this->proTenant();
});

/** @return list<array<string, mixed>> */
function programExercises(array $payload): array
{
    $out = [];
    foreach ($payload['days'] as $day) {
        foreach ($day['workout']['blocks'] as $block) {
            array_push($out, ...$block['exercises']);
        }
    }

    return $out;
}

it('drafts a program from free text using only library exercises', function () {
    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/programs', [
        'prompt' => '4-day hypertrophy, dumbbells only, 45 min',
    ])->assertCreated();

    $response->assertJsonPath('data.feature', 'program')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.payload.days_per_week', 4)
        ->assertJsonPath('data.payload.goal', 'muscle_gain');

    $payload = $response->json('data.payload');
    $libraryIds = Exercise::query()->pluck('id')->all();
    $exercises = programExercises($payload);
    expect($exercises)->not->toBeEmpty();
    foreach ($exercises as $e) {
        expect($libraryIds)->toContain($e['exercise_id']);
        $equipment = Exercise::query()->find($e['exercise_id'])->equipment ?? [];
        expect(array_diff($equipment, ['dumbbell', 'bodyweight']))->toBe([]);
    }
    expect($response->json('data.grounding.label'))->toContain('0');
});

it('requires coach approval before a real program exists, then applies it', function () {
    $outputId = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/programs', [
        'prompt' => '3 day full body beginner with dumbbells', 'weeks' => 4,
    ])->assertCreated()->json('data.id');

    expect(Program::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->count())->toBe(0);

    $this->postJson("/api/v1/ai/outputs/{$outputId}/approve", ['apply' => true])
        ->assertOk()
        ->assertJsonPath('data.status', 'applied')
        ->assertJsonPath('data.applied.type', 'program');

    $program = Program::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->firstOrFail();
    expect($program->days_per_week)->toBe(3)
        ->and($program->weeks()->count())->toBe(4)
        ->and($program->weeks()->where('is_deload', true)->count())->toBe(1)
        ->and(ProgramDay::query()->whereIn('program_week_id', $program->weeks()->pluck('id'))->count())->toBe(28)
        ->and(Workout::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->count())->toBe(3)
        ->and(AiOutput::query()->withoutGlobalScopes()->find($outputId)->applied_id)->toBe($program->id);

    $exerciseIds = WorkoutExercise::query()->pluck('exercise_id')->unique();
    expect(Exercise::query()->whereIn('id', $exerciseIds)->count())->toBe($exerciseIds->count());
});

it('maps unknown exercise names to the closest library exercise', function () {
    $this->fake()->push('program', function ($prompt) {
        $data = app(\App\Domain\Ai\Providers\Fake\FakeResponder::class)->structured('program', $prompt->context);
        $data['days'][0]['workout']['blocks'][0]['exercises'][0]['exercise_id'] = 'not-a-real-id';
        $data['days'][0]['workout']['blocks'][0]['exercises'][0]['exercise_name'] = 'goblet squats with a dumbbell';

        return $data;
    });

    $payload = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/programs', [
        'prompt' => '3 day full body, dumbbells',
    ])->assertCreated()->json('data.payload');

    expect($payload['mapped_exercises'][0]['to_id'])->toBe($this->exercises['goblet-squat']->id)
        ->and($payload['days'][0]['workout']['blocks'][0]['exercises'][0]['exercise_id'])->toBe($this->exercises['goblet-squat']->id);
});

it('retries with validation feedback when the output is invalid, and meters each attempt', function () {
    $this->fake()->push('program', ['name' => 'Broken']); // schema-invalid
    $this->fake()->push('program', function ($prompt) {
        $data = app(\App\Domain\Ai\Providers\Fake\FakeResponder::class)->structured('program', $prompt->context);
        $data['days'][0]['workout']['blocks'][0]['exercises'][0]['exercise_id'] = 'nope';
        $data['days'][0]['workout']['blocks'][0]['exercises'][0]['exercise_name'] = 'xyzzy plyometric tornado';

        return $data;
    });

    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/programs', ['prompt' => '3 day dumbbell program'])->assertCreated();

    $statuses = AiRequest::query()->where('tenant_id', $this->tenant->id)->where('feature', 'program')->orderBy('created_at')->orderBy('id')->pluck('status')->all();
    expect($statuses)->toHaveCount(3)
        ->and(array_count_values($statuses))->toBe(['invalid' => 2, 'ok' => 1]);

    $calls = $this->fake()->callsFor('program');
    $messages = end($calls)['prompt']->messages;
    expect(end($messages)['content'])->toContain('not in the exercise library');
});

it('fails cleanly after the maximum number of retries', function () {
    foreach (range(1, 3) as $i) {
        $this->fake()->push('program', ['invalid' => true]);
    }

    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/programs', ['prompt' => '3 day program']);

    expectApiError($response, 502, 'ai_invalid_output');
    expect(AiRequest::query()->where('feature', 'program')->where('status', 'invalid')->count())->toBe(3)
        ->and(AiOutput::query()->withoutGlobalScopes()->count())->toBe(0);
});

it('grounds the draft on the client and flags injuries for the coach', function () {
    $client = $this->clientWithProfile($this->tenant, $this->coach, ['goal' => 'strength', 'level' => 'intermediate', 'days_per_week' => 3, 'equipment' => ['barbell', 'bench', 'rack'], 'injuries' => 'Lower back pain when deadlifting']);

    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/programs', ['client_id' => $client->id])->assertCreated();

    expect($response->json('data.client.id'))->toBe($client->id)
        ->and($response->json('data.payload.goal'))->toBe('strength')
        ->and(collect($response->json('data.flags'))->pluck('type'))->toContain('injury');

    $prompt = $this->fake()->callsFor('program')[0]['prompt'];
    expect($prompt->messages[0]['content'])->not->toContain($client->email)
        ->and($prompt->messages[0]['content'])->toContain($client->firstName());
});

it('rejects clients of another tenant', function () {
    [$other, $otherOwner] = $this->proTenant();
    $foreign = $this->addClient($other, $otherOwner);

    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/programs', ['client_id' => $foreign->id]);
    expectApiError($response, 404, 'not_found');
});
