<?php

use App\Domain\Ai\Providers\Fake\FakeResponder;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\ProgramWeek;
use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutBlock;
use App\Domain\Workouts\Models\WorkoutExercise;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Domain\Workouts\Models\WorkoutSetTemplate;
use Tests\Feature\Ai\Support\AiFixtures;

uses(AiFixtures::class);

beforeEach(function () {
    $this->ex = $this->seedExercises();
    [$this->tenant, $this->coach] = $this->proTenant();
    $this->client = $this->addClient($this->tenant, $this->coach);

    $this->inTenant($this->tenant, function () {
        // Easy squats (RPE 7, all completed) and grinding rows (RPE 9.5, missed reps).
        foreach ([21, 14, 7, 2] as $i => $daysAgo) {
            $session = WorkoutSession::query()->create(['client_id' => $this->client->id, 'status' => 'completed', 'started_at' => now()->subDays($daysAgo)]);
            foreach (range(1, 3) as $n) {
                SessionSet::query()->create(['workout_session_id' => $session->id, 'exercise_id' => $this->ex['goblet-squat']->id, 'set_number' => $n, 'reps' => 10, 'weight_kg' => 20 + $i * 2, 'rpe' => 7, 'is_completed' => true, 'completed_at' => now()->subDays($daysAgo)]);
                SessionSet::query()->create(['workout_session_id' => $session->id, 'exercise_id' => $this->ex['db-row']->id, 'set_number' => $n, 'reps' => 6, 'weight_kg' => 30, 'rpe' => 9.5, 'is_completed' => $n < 3, 'completed_at' => now()->subDays($daysAgo)]);
            }
        }

        $program = Program::query()->create(['name' => ['en' => 'Client plan'], 'days_per_week' => 1]);
        $week = ProgramWeek::query()->create(['program_id' => $program->id, 'number' => 1]);
        $workout = Workout::query()->create(['name' => 'Day A']);
        $block = WorkoutBlock::query()->create(['workout_id' => $workout->id, 'position' => 1]);
        foreach (['goblet-squat' => 26, 'db-row' => 30] as $slug => $kg) {
            $we = WorkoutExercise::query()->create(['workout_block_id' => $block->id, 'exercise_id' => $this->ex[$slug]->id, 'position' => 1]);
            foreach (range(1, 3) as $n) {
                WorkoutSetTemplate::query()->create(['workout_exercise_id' => $we->id, 'position' => $n, 'reps_min' => 8, 'reps_max' => 10, 'weight_kg' => $kg]);
            }
        }
        ProgramDay::query()->create(['program_week_id' => $week->id, 'day_number' => 1, 'workout_id' => $workout->id]);
        ProgramAssignment::query()->create(['program_id' => $program->id, 'client_id' => $this->client->id, 'start_date' => now()->subDays(30)->toDateString(), 'status' => 'active']);
    });
});

it('analyses four weeks of logs into a per-exercise diff', function () {
    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/ai/progression/{$this->client->id}")->assertCreated();

    $suggestions = collect($response->json('data.payload.suggestions'))->keyBy('exercise_id');
    $squat = $suggestions[$this->ex['goblet-squat']->id];
    $row = $suggestions[$this->ex['db-row']->id];

    expect($squat['action'])->toBe('increase_load')
        ->and($squat['current']['weight_kg'])->toEqual(26)
        ->and($squat['proposed']['weight_kg'])->toEqual(27)
        ->and($row['action'])->toBe('decrease_load')
        ->and($row['proposed']['weight_kg'])->toEqual(28.5)
        ->and($response->json('data.grounding.sessions'))->toBe(4);
});

it('caps load jumps at 10% and applies approved changes to the client program', function () {
    $this->fake()->push('progression', function ($prompt) {
        $data = app(FakeResponder::class)->structured('progression', $prompt->context);
        foreach ($data['suggestions'] as &$s) {
            if ($s['action'] === 'increase_load') {
                $s['proposed']['weight_kg'] = 40; // +54% — must be capped
            }
        }

        return $data;
    });

    $this->actingAsIn($this->coach, $this->tenant);
    $output = $this->postJson("/api/v1/ai/progression/{$this->client->id}")->assertCreated()->json('data');
    $squat = collect($output['payload']['suggestions'])->firstWhere('exercise_id', $this->ex['goblet-squat']->id);
    expect($squat['proposed']['weight_kg'])->toEqual(28.5)->and($squat['capped'])->toBeTrue();

    $this->postJson("/api/v1/ai/outputs/{$output['id']}/approve", ['apply' => true])->assertOk()->assertJsonPath('data.status', 'applied');

    $weights = WorkoutSetTemplate::query()->get()->groupBy(fn ($t) => WorkoutExercise::query()->find($t->workout_exercise_id)->exercise_id)->map(fn ($g) => $g->pluck('weight_kg')->unique()->values()->all());
    expect($weights[$this->ex['goblet-squat']->id])->toEqual([28.5])
        ->and($weights[$this->ex['db-row']->id])->toEqual([28.5]);
});

it('needs logged sessions and a client of this tenant', function () {
    $fresh = $this->addClient($this->tenant, $this->coach);
    $this->actingAsIn($this->coach, $this->tenant);
    expectApiError($this->postJson("/api/v1/ai/progression/{$fresh->id}"), 422, 'not_enough_data');

    [$other, $otherOwner] = $this->proTenant();
    $this->actingAsIn($otherOwner, $other);
    expectApiError($this->postJson("/api/v1/ai/progression/{$this->client->id}"), 404, 'not_found');
});
