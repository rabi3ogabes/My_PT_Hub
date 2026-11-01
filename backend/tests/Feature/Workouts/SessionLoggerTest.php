<?php

use App\Domain\Workouts\Events\PersonalRecordAchieved;
use App\Domain\Workouts\Events\WorkoutCompleted;
use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Domain\Workouts\Services\ProgramAssigner;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\Feature\Workouts\Fixtures;

beforeEach(function () {
    Carbon::setTestNow('2026-11-02 08:00:00');
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner, ['timezone' => 'UTC']);
    $this->ex = Fixtures::library();
    [$this->workout, $this->program] = $this->inTenant($this->tenant, function () {
        $w = Fixtures::workout($this->ex, $this->owner->id);
        $p = Fixtures::program($w);
        app(ProgramAssigner::class)->assign($p, $this->client, '2026-11-02');

        return [$w, $p];
    });
    $this->actingAsIn($this->client, $this->tenant);
});

afterEach(fn () => Carbon::setTestNow());

function workoutsUlid(): string
{
    return strtolower((string) Str::ulid());
}

function workoutsSet(string $exerciseId, int $n, float $kg, int $reps, ?string $id = null): array
{
    return ['id' => $id ?? workoutsUlid(), 'exercise_id' => $exerciseId, 'set_number' => $n, 'set_type' => 'working', 'reps' => $reps, 'weight_kg' => $kg, 'is_completed' => true, 'completed_at' => now()->toIso8601String()];
}

it('starts sessions idempotently on the client ULID', function () {
    $id = workoutsUlid();
    $first = $this->postJson('/api/v1/me/sessions', ['id' => $id, 'workout_id' => $this->workout->id, 'started_at' => '2026-11-02T07:00:00Z'])->assertCreated();
    expect($first->json('data'))->toHaveKeys(['id', 'workout_id', 'program_day_id', 'name', 'status', 'started_at', 'ended_at', 'duration_seconds', 'total_volume_kg', 'perceived_effort', 'mood', 'notes', 'sets', 'personal_records'])
        ->and($first->json('data.name'))->toBe('Push Day')
        ->and($first->json('data.status'))->toBe('in_progress');

    $this->postJson('/api/v1/me/sessions', ['id' => $id, 'workout_id' => $this->workout->id])->assertOk()->assertJsonPath('data.id', $id);
    expect(WorkoutSession::count())->toBe(1);
});

it('upserts sets idempotently: the same set twice is one row and edits win', function () {
    $sid = $this->postJson('/api/v1/me/sessions', ['id' => workoutsUlid()])->json('data.id');
    $set = workoutsSet($this->ex['bench']->id, 1, 60, 5);

    $this->postJson("/api/v1/me/sessions/{$sid}/sets", ['sets' => [$set]])->assertOk()->assertJsonPath('data.sets.0.id', $set['id']);
    $this->postJson("/api/v1/me/sessions/{$sid}/sets", ['sets' => [$set, $set]])->assertOk();
    expect(SessionSet::count())->toBe(1);

    $set['reps'] = 6;
    $this->postJson("/api/v1/me/sessions/{$sid}/sets", ['sets' => [$set, workoutsSet($this->ex['bench']->id, 2, 60, 5)]])->assertOk();
    expect(SessionSet::count())->toBe(2)->and(SessionSet::find($set['id'])->reps)->toBe(6);
});

it('completes a session: duration, volume, Epley PRs and events (idempotent)', function () {
    Event::fake([WorkoutCompleted::class, PersonalRecordAchieved::class]);
    $bench = $this->ex['bench']->id;

    // Previous session: 100 kg × 5 (e1RM 116.67), volume 1000.
    $prev = $this->postJson('/api/v1/me/sessions', ['id' => workoutsUlid(), 'started_at' => '2026-10-28T07:00:00Z'])->json('data.id');
    $this->postJson("/api/v1/me/sessions/{$prev}/sets", ['sets' => [workoutsSet($bench, 1, 100, 5), workoutsSet($bench, 2, 100, 5)]])->assertOk();
    $prevDone = $this->postJson("/api/v1/me/sessions/{$prev}/complete", ['ended_at' => '2026-10-28T08:00:00Z'])->assertOk();
    expect($prevDone->json('data.personal_records'))->toBe([]); // first time = baseline only
    Event::assertDispatchedTimes(WorkoutCompleted::class, 1);
    Event::assertNotDispatched(PersonalRecordAchieved::class);

    // Today: 102.5 × 5 (e1RM PR, weight PR), 90 × 8 (rep PR: previous best at ≥ 90 kg was 5 reps), warm-up ignored.
    $sid = $this->postJson('/api/v1/me/sessions', ['id' => workoutsUlid(), 'workout_id' => $this->workout->id, 'started_at' => '2026-11-02T07:00:00Z'])->json('data.id');
    $warm = workoutsSet($bench, 1, 40, 10);
    $warm['set_type'] = 'warmup';
    $this->postJson("/api/v1/me/sessions/{$sid}/sets", ['sets' => [$warm, workoutsSet($bench, 2, 102.5, 5), workoutsSet($bench, 3, 90, 8)]])->assertOk();

    $res = $this->postJson("/api/v1/me/sessions/{$sid}/complete", ['ended_at' => '2026-11-02T07:45:30Z', 'perceived_effort' => 8, 'mood' => 'great'])->assertOk();
    $data = $res->json('data');
    expect($data['status'])->toBe('completed')
        ->and($data['duration_seconds'])->toBe(2730)
        ->and($data['total_volume_kg'])->toEqual(1232.5)
        ->and($data['perceived_effort'])->toBe(8);

    $prs = collect($data['personal_records'])->keyBy('type');
    expect($prs->keys()->sort()->values()->all())->toBe(['e1rm', 'reps', 'volume', 'weight'])
        ->and($prs['e1rm']['value'])->toEqual(round(102.5 * (1 + 5 / 30), 2))
        ->and($prs['e1rm']['previous_value'])->toEqual(round(100 * (1 + 5 / 30), 2))
        ->and($prs['weight']['value'])->toEqual(102.5)
        ->and($prs['weight']['previous_value'])->toEqual(100)
        ->and($prs['reps']['value'])->toEqual(8) // 90 kg × 8 beats the previous 5 reps at ≥ 90 kg
        ->and($prs['reps']['weight_kg'])->toEqual(90)
        ->and($prs['reps']['previous_value'])->toEqual(5)
        ->and($prs['volume']['value'])->toEqual(1232.5)
        ->and($prs['volume']['previous_value'])->toEqual(1000)
        ->and($prs['e1rm']['exercise_name'])->toBe('Barbell Bench Press');

    Event::assertDispatchedTimes(WorkoutCompleted::class, 2);
    Event::assertDispatchedTimes(PersonalRecordAchieved::class, 4);

    // Idempotent complete: no new events, same result.
    $this->postJson("/api/v1/me/sessions/{$sid}/complete")->assertOk()->assertJsonPath('data.duration_seconds', 2730);
    Event::assertDispatchedTimes(WorkoutCompleted::class, 2);
    expect(PersonalRecord::where('workout_session_id', $sid)->count())->toBe(4);

    // Today screen now shows the workout as completed.
    $this->postJson('/api/v1/me/sessions', ['id' => workoutsUlid(), 'program_day_id' => app(\App\Domain\Workouts\Services\ProgramSchedule::class)->dayFor(
        \App\Domain\Workouts\Models\ProgramAssignment::withoutGlobalScopes()->first(), '2026-11-02')->id])->assertCreated();
});

it('serves history, last performance, records and the client workout view', function () {
    $bench = $this->ex['bench']->id;
    $sid = $this->postJson('/api/v1/me/sessions', ['id' => workoutsUlid(), 'started_at' => '2026-10-30T07:00:00Z'])->json('data.id');
    $this->postJson("/api/v1/me/sessions/{$sid}/sets", ['sets' => [workoutsSet($bench, 1, 60, 5), workoutsSet($bench, 2, 60, 5), workoutsSet($bench, 3, 60, 5)]]);
    $this->postJson("/api/v1/me/sessions/{$sid}/complete");

    $last = $this->getJson("/api/v1/me/exercises/{$bench}/last")->assertOk()->json('data');
    expect($last['session_id'])->toBe($sid)->and($last['sets'])->toHaveCount(3)->and($last['sets'][0])->toBe(['set_number' => 1, 'reps' => 5, 'weight_kg' => 60, 'duration_seconds' => null, 'distance_m' => null, 'rpe' => null]);

    $history = $this->getJson("/api/v1/me/exercises/{$bench}/history")->assertOk()->json('data');
    expect($history['best_weight_kg'])->toEqual(60)->and($history['best_e1rm_kg'])->toEqual(70)
        ->and($history['sessions'][0])->toMatchArray(['session_id' => $sid, 'date' => '2026-10-30', 'volume_kg' => 900, 'e1rm_kg' => 70]);

    $records = $this->getJson('/api/v1/me/records')->assertOk()->json('data');
    expect(collect($records)->pluck('type')->sort()->values()->all())->toBe(['e1rm', 'reps', 'volume', 'weight']);

    // Workout view: last performance + linear progression (all 5×5 hit at 60 → 62.5).
    $view = $this->getJson("/api/v1/me/workouts/{$this->workout->id}")->assertOk()->json('data');
    $benchEx = $view['blocks'][1]['exercises'][0];
    expect($view)->toHaveKey('download_size_bytes')
        ->and($benchEx['last_performance']['session_id'])->toBe($sid)
        ->and(collect($benchEx['sets'])->pluck('weight_kg')->unique()->values()->all())->toEqual([62.5])
        ->and($this->getJson('/api/v1/me/exercises/'.$this->ex['squat']->id.'/last')->json('data'))->toBeNull();
});

it('re-detects records when sets sync after completion', function () {
    $bench = $this->ex['bench']->id;
    $prev = $this->postJson('/api/v1/me/sessions', ['id' => workoutsUlid(), 'started_at' => '2026-10-28T07:00:00Z'])->json('data.id');
    $this->postJson("/api/v1/me/sessions/{$prev}/sets", ['sets' => [workoutsSet($bench, 1, 80, 5)]]);
    $this->postJson("/api/v1/me/sessions/{$prev}/complete");

    $sid = $this->postJson('/api/v1/me/sessions', ['id' => workoutsUlid()])->json('data.id');
    $this->postJson("/api/v1/me/sessions/{$sid}/sets", ['sets' => [workoutsSet($bench, 1, 70, 5)]]);
    expect($this->postJson("/api/v1/me/sessions/{$sid}/complete")->json('data.personal_records'))->toBe([]);

    $this->postJson("/api/v1/me/sessions/{$sid}/sets", ['sets' => [workoutsSet($bench, 2, 85, 3)]])->assertOk();
    $session = $this->getJson("/api/v1/me/sessions/{$sid}")->json('data');
    expect($session['total_volume_kg'])->toEqual(605)
        ->and(collect($session['personal_records'])->pluck('type')->all())->toContain('weight');
});

it('never lets a client read another client’s session or another tenant read it', function () {
    $sid = $this->postJson('/api/v1/me/sessions', ['id' => workoutsUlid()])->json('data.id');

    $other = $this->addClient($this->tenant, $this->owner);
    $this->actingAsIn($other, $this->tenant);
    expectApiError($this->getJson("/api/v1/me/sessions/{$sid}"), 404, 'not_found');
    expectApiError($this->postJson("/api/v1/me/sessions/{$sid}/sets", ['sets' => [workoutsSet($this->ex['bench']->id, 1, 50, 5)]]), 404, 'not_found');
    expectApiError($this->postJson("/api/v1/me/sessions/{$sid}/complete"), 404, 'not_found');
    expect($this->getJson('/api/v1/me/sessions')->json('data'))->toBeEmpty();
    // Re-using another client's session id is a conflict, never a takeover.
    expectApiError($this->postJson('/api/v1/me/sessions', ['id' => $sid]), 409, 'session_conflict');

    [$tenantB, $ownerB] = $this->createCoachTenant();
    $clientB = $this->addClient($tenantB, $ownerB);
    $this->actingAsIn($clientB, $tenantB);
    expectApiError($this->getJson("/api/v1/me/sessions/{$sid}"), 404, 'not_found');
    expectApiError($this->getJson("/api/v1/me/workouts/{$this->workout->id}"), 404, 'not_found');
});
