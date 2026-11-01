<?php

use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\WaterLog;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Models\WorkoutSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\Feature\Workouts\Fixtures;

beforeEach(function () {
    Carbon::setTestNow('2026-11-02 08:00:00');
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner, ['timezone' => 'UTC']);
    $this->ex = Fixtures::library();
    $this->actingAsIn($this->client, $this->tenant);
});

afterEach(fn () => Carbon::setTestNow());

function syncUlid(): string
{
    return strtolower((string) Str::ulid());
}

function syncMutation(string $entity, array $payload, string $op = 'upsert', ?string $id = null): array
{
    return ['id' => $id ?? syncUlid(), 'entity' => $entity, 'op' => $op, 'payload' => $payload, 'client_time' => now()->toIso8601String()];
}

it('applies an offline workout idempotently: same mutation twice = one set', function () {
    $sessionId = syncUlid();
    $setId = syncUlid();
    $session = syncMutation('session', ['id' => $sessionId, 'name' => 'Offline push', 'started_at' => '2026-11-02T06:00:00Z']);
    $set = syncMutation('session_set', ['id' => $setId, 'session_id' => $sessionId, 'exercise_id' => $this->ex['bench']->id, 'set_number' => 1, 'reps' => 5, 'weight_kg' => 80]);

    $first = $this->postJson('/api/v1/sync/push', ['mutations' => [$session, $set]])->assertOk();
    expect(collect($first->json('data.results'))->pluck('status')->all())->toBe(['applied', 'applied'])
        ->and($first->json('data.server_time'))->toBeString();

    // Replay (network retry) → duplicates, still exactly one set.
    $replay = $this->postJson('/api/v1/sync/push', ['mutations' => [$session, $set]])->assertOk();
    expect(collect($replay->json('data.results'))->pluck('status')->all())->toBe(['duplicate', 'duplicate']);

    // A new mutation for the same set id (different mutation id) is an upsert, not a new row.
    $edit = syncMutation('session_set', ['id' => $setId, 'session_id' => $sessionId, 'exercise_id' => $this->ex['bench']->id, 'set_number' => 1, 'reps' => 6, 'weight_kg' => 80]);
    $complete = syncMutation('session', ['id' => $sessionId, 'status' => 'completed', 'ended_at' => '2026-11-02T07:00:00Z', 'perceived_effort' => 7]);
    $this->postJson('/api/v1/sync/push', ['mutations' => [$edit, $complete]])->assertOk();

    expect(SessionSet::count())->toBe(1)
        ->and(SessionSet::first()->reps)->toBe(6)
        ->and(WorkoutSession::first()->status)->toBe('completed')
        ->and(WorkoutSession::first()->duration_seconds)->toBe(3600)
        ->and(WorkoutSession::first()->total_volume_kg)->toEqual(480);
});

it('handles nutrition, water, habits and body metrics and rejects messages', function () {
    [$assignment] = $this->inTenant($this->tenant, function () {
        $habit = Habit::create(['tenant_id' => null, 'name' => ['en' => 'Water'], 'type' => 'count', 'target' => 8]);

        return [HabitAssignment::create(['habit_id' => $habit->id, 'client_id' => $this->client->id, 'start_date' => '2026-10-01'])];
    });
    $foodId = syncUlid();
    $res = $this->postJson('/api/v1/sync/push', ['mutations' => [
        syncMutation('food_log', ['id' => $foodId, 'log_date' => '2026-11-02', 'slot' => 'breakfast', 'name' => 'Labneh sandwich', 'quantity' => 1, 'unit' => 'serving', 'kcal' => 420, 'protein_g' => 18]),
        syncMutation('water_log', ['id' => syncUlid(), 'log_date' => '2026-11-02', 'ml' => 500]),
        syncMutation('habit_log', ['assignment_id' => $assignment->id, 'date' => '2026-11-02', 'value' => 9]),
        syncMutation('body_metric', ['id' => syncUlid(), 'measured_on' => '2026-11-02', 'weight_kg' => 82.4, 'measurements' => ['waist' => 84]]),
        syncMutation('message', ['body' => 'hi']),
        syncMutation('food_log', ['id' => syncUlid(), 'log_date' => 'yesterday', 'slot' => 'x', 'quantity' => 1]),
    ]])->assertOk();

    $results = $res->json('data.results');
    expect(collect($results)->pluck('status')->all())->toBe(['applied', 'applied', 'applied', 'applied', 'rejected', 'rejected'])
        ->and($results[4]['error'])->toBe('use_messages_endpoint')
        ->and($results[5]['error'])->toBe('validation_failed');

    expect(FoodLog::withoutGlobalScopes()->find($foodId)->kcal)->toEqual(420)
        ->and(WaterLog::withoutGlobalScopes()->sum('ml'))->toEqual(500)
        ->and(HabitLog::first()->completed)->toBeTrue()
        ->and(BodyMetric::withoutGlobalScopes()->first()->weight_kg)->toEqual(82.4);

    // Delete the food log → tombstone in pull.
    $this->postJson('/api/v1/sync/push', ['mutations' => [syncMutation('food_log', ['id' => $foodId], 'delete')]])->assertOk()
        ->assertJsonPath('data.results.0.status', 'applied');
    $pull = $this->getJson('/api/v1/sync/pull?since='.urlencode('2026-11-02T07:00:00Z'))->assertOk();
    expect($pull->json('data.deleted.food_logs'))->toBe([$foodId]);
});

it('pulls only the authenticated client’s changes since a timestamp', function () {
    $sessionId = syncUlid();
    $this->postJson('/api/v1/sync/push', ['mutations' => [
        syncMutation('session', ['id' => $sessionId, 'started_at' => '2026-11-02T06:00:00Z']),
        syncMutation('session_set', ['id' => syncUlid(), 'session_id' => $sessionId, 'exercise_id' => $this->ex['squat']->id, 'reps' => 5, 'weight_kg' => 100]),
        syncMutation('water_log', ['id' => syncUlid(), 'log_date' => '2026-11-02', 'ml' => 250]),
    ]])->assertOk();

    // Another client in the same tenant logs too.
    $other = $this->addClient($this->tenant, $this->owner);
    $this->actingAsIn($other, $this->tenant)->postJson('/api/v1/sync/push', ['mutations' => [syncMutation('water_log', ['id' => syncUlid(), 'log_date' => '2026-11-02', 'ml' => 999])]])->assertOk();

    $pull = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/sync/pull')->assertOk();
    $changes = $pull->json('data.changes');
    expect(array_keys($changes))->toBe(['sessions', 'session_sets', 'food_logs', 'water_logs', 'habit_logs', 'body_metrics', 'bookings'])
        ->and($changes['sessions'][0]['id'])->toBe($sessionId)
        ->and($changes['session_sets'])->toHaveCount(1)
        ->and($changes['session_sets'][0]['session_id'])->toBe($sessionId)
        ->and(collect($changes['water_logs'])->pluck('ml')->all())->toBe([250])
        ->and($pull->json('data.server_time'))->toBeString();

    Carbon::setTestNow('2026-11-02 10:00:00');
    $later = $this->getJson('/api/v1/sync/pull?since='.urlencode('2026-11-02T09:00:00Z'))->assertOk();
    expect($later->json('data.changes.sessions'))->toBe([])->and($later->json('data.changes.water_logs'))->toBe([]);

    // Deleting a set through sync → soft delete tombstone; the row is kept.
    $setId = $changes['session_sets'][0]['id'];
    $this->postJson('/api/v1/sync/push', ['mutations' => [syncMutation('session_set', ['id' => $setId, 'session_id' => $sessionId], 'delete')]])->assertOk();
    expect($this->getJson('/api/v1/sync/pull?since='.urlencode('2026-11-02T09:30:00Z'))->json('data.deleted.session_sets'))->toBe([$setId])
        ->and(SessionSet::withTrashed()->count())->toBe(1);
});

it('rejects sync writes into another client’s session', function () {
    $sessionId = syncUlid();
    $this->postJson('/api/v1/sync/push', ['mutations' => [syncMutation('session', ['id' => $sessionId])]])->assertOk();

    $other = $this->addClient($this->tenant, $this->owner);
    $res = $this->actingAsIn($other, $this->tenant)->postJson('/api/v1/sync/push', ['mutations' => [
        syncMutation('session_set', ['id' => syncUlid(), 'session_id' => $sessionId, 'exercise_id' => $this->ex['bench']->id, 'reps' => 1, 'weight_kg' => 1]),
        syncMutation('session', ['id' => $sessionId, 'name' => 'stolen']),
    ]])->assertOk();
    expect(collect($res->json('data.results'))->pluck('status')->all())->toBe(['rejected', 'rejected'])
        ->and(SessionSet::count())->toBe(0)
        ->and(WorkoutSession::withoutGlobalScopes()->find($sessionId)->name)->not->toBe('stolen');
});
