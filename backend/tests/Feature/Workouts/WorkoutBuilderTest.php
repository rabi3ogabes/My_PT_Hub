<?php

use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutBlock;
use App\Domain\Workouts\Models\WorkoutExercise;
use App\Domain\Workouts\Models\WorkoutSetTemplate;
use App\Domain\Workouts\Services\WorkoutBuilder;
use Illuminate\Database\Eloquent\Model;
use Tests\Feature\Workouts\Fixtures;

beforeEach(function () {
    Model::preventLazyLoading(); // catch N+1 / lazy loads (dev behaviour)
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->ex = Fixtures::library();
    $this->actingAsIn($this->owner, $this->tenant);
});

it('creates a nested workout with every block type and computes duration and equipment', function () {
    $payload = Fixtures::workoutPayload($this->ex);
    foreach (['giant', 'circuit', 'emom', 'amrap', 'interval', 'cooldown'] as $type) {
        $payload['blocks'][] = ['type' => $type, 'rounds' => 3, 'work_seconds' => 40, 'rest_seconds' => 20, 'time_cap_seconds' => $type === 'amrap' ? 600 : null,
            'exercises' => [['exercise_id' => $this->ex['squat']->id, 'sets' => [['reps_min' => 10]]]]];
    }

    $res = $this->postJson('/api/v1/workouts', $payload)->assertCreated();
    $data = $res->json('data');

    expect(collect($data['blocks'])->pluck('type')->all())->toBe(['warmup', 'straight', 'superset', 'tabata', 'giant', 'circuit', 'emom', 'amrap', 'interval', 'cooldown'])
        ->and(collect($data['blocks'])->pluck('position')->all())->toBe(range(1, 10))
        ->and($data['estimated_minutes'])->toBeGreaterThan(20)
        ->and($data['equipment_summary'])->toEqualCanonicalizing(['barbell', 'bench', 'dumbbell'])
        ->and($data['blocks'][1]['exercises'][0]['sets'])->toHaveCount(3)
        ->and($data['blocks'][1]['exercises'][0]['exercise']['slug'])->toBe('barbell-bench-press')
        ->and(array_keys($data['blocks'][1]['exercises'][0]['sets'][0]))->toEqual(['id', 'position', 'set_type', 'reps_min', 'reps_max', 'weight_kg', 'percent_1rm', 'rpe', 'rir', 'duration_seconds', 'distance_m', 'rest_seconds']);
});

it('fully replaces blocks on update preserving the new order and reusing sent ids', function () {
    $created = $this->postJson('/api/v1/workouts', Fixtures::workoutPayload($this->ex))->assertCreated()->json('data');
    $blocks = $created['blocks'];

    // Reverse the blocks, drop the tabata, reorder exercises inside the superset and remove one set.
    $superset = $blocks[2];
    $superset['exercises'] = array_reverse($superset['exercises']);
    $straight = $blocks[1];
    array_pop($straight['exercises'][0]['sets']);
    $toPayload = fn (array $b) => [
        'id' => $b['id'], 'type' => $b['type'], 'name' => $b['name'], 'rounds' => $b['rounds'], 'rest_between_rounds' => $b['rest_between_rounds'],
        'exercises' => array_map(fn ($e) => [
            'id' => $e['id'], 'exercise_id' => $e['exercise']['id'], 'rest_seconds' => $e['rest_seconds'],
            'sets' => array_map(fn ($s) => array_intersect_key($s, array_flip(['id', 'set_type', 'reps_min', 'reps_max', 'weight_kg'])), $e['sets']),
        ], $b['exercises']),
    ];

    $res = $this->putJson('/api/v1/workouts/'.$created['id'], [
        'name' => 'Push Day v2',
        'blocks' => [$toPayload($superset), $toPayload($straight), $toPayload($blocks[0])],
    ])->assertOk();
    $data = $res->json('data');

    expect($data['name'])->toBe('Push Day v2')
        ->and(collect($data['blocks'])->pluck('type')->all())->toBe(['superset', 'straight', 'warmup'])
        ->and(collect($data['blocks'])->pluck('position')->all())->toBe([1, 2, 3])
        ->and($data['blocks'][0]['id'])->toBe($superset['id'])
        ->and(collect($data['blocks'][0]['exercises'])->pluck('exercise.slug')->all())->toBe(['push-up', 'dumbbell-bench-press'])
        ->and(collect($data['blocks'][0]['exercises'])->pluck('position')->all())->toBe([1, 2])
        ->and($data['blocks'][1]['exercises'][0]['sets'])->toHaveCount(2)
        ->and(collect($data['blocks'][1]['exercises'][0]['sets'])->pluck('position')->all())->toBe([1, 2])
        ->and($data['equipment_summary'])->toEqualCanonicalizing(['barbell', 'bench', 'dumbbell'])
        ->and($data['version'])->toBe(2);

    // No orphans left behind.
    $blockIds = WorkoutBlock::where('workout_id', $created['id'])->pluck('id');
    expect($blockIds)->toHaveCount(3)
        ->and(WorkoutExercise::whereIn('workout_block_id', $blockIds)->count())->toBe(4)
        ->and(WorkoutSetTemplate::count())->toBe(WorkoutSetTemplate::whereIn('workout_exercise_id', WorkoutExercise::whereIn('workout_block_id', $blockIds)->pluck('id'))->count());
});

it('rolls back the whole update when an exercise is invalid', function () {
    $created = $this->postJson('/api/v1/workouts', Fixtures::workoutPayload($this->ex))->assertCreated()->json('data');
    $bad = Fixtures::workoutPayload($this->ex);
    $bad['blocks'][1]['exercises'][0]['exercise_id'] = '01JZZZZZZZZZZZZZZZZZZZZZZZ';

    expectApiError($this->putJson('/api/v1/workouts/'.$created['id'], $bad), 422, 'validation_failed');
    expect(WorkoutBlock::where('workout_id', $created['id'])->count())->toBe(4);
});

it('duplicates, exports and imports workouts', function () {
    $created = $this->postJson('/api/v1/workouts', Fixtures::workoutPayload($this->ex))->assertCreated()->json('data');

    $copy = $this->postJson('/api/v1/workouts/'.$created['id'].'/duplicate')->assertCreated()->json('data');
    expect($copy['id'])->not->toBe($created['id'])
        ->and($copy['name'])->toBe('Push Day (copy)')
        ->and(count($copy['blocks']))->toBe(4);

    $export = $this->getJson('/api/v1/workouts/'.$created['id'].'/export')->assertOk()->json('data');
    expect($export['format'])->toBe('aurum.workout.v1')
        ->and($export['blocks'][1]['exercises'][0]['exercise_slug'])->toBe('barbell-bench-press');

    // Import in another tenant: ids are unknown there for custom exercises, slugs resolve global ones.
    [$tenantB, $ownerB] = $this->createCoachTenant();
    $export['blocks'][1]['exercises'][0]['exercise_id'] = null;
    $imported = $this->actingAsIn($ownerB, $tenantB)->postJson('/api/v1/workouts/import', $export)->assertCreated()->json('data');
    expect($imported['blocks'][1]['exercises'][0]['exercise']['id'])->toBe($this->ex['bench']->id)
        ->and($imported['estimated_minutes'])->toBe($created['estimated_minutes']);
});

it('isolates workouts between tenants (404) and keeps global templates read-only', function () {
    $created = $this->postJson('/api/v1/workouts', Fixtures::workoutPayload($this->ex))->assertCreated()->json('data');

    [$tenantB, $ownerB] = $this->createCoachTenant();
    $this->actingAsIn($ownerB, $tenantB);
    expectApiError($this->getJson('/api/v1/workouts/'.$created['id']), 404, 'not_found');
    expectApiError($this->putJson('/api/v1/workouts/'.$created['id'], ['name' => 'Mine now']), 404, 'not_found');
    expectApiError($this->deleteJson('/api/v1/workouts/'.$created['id']), 404, 'not_found');
    expect(collect($this->getJson('/api/v1/workouts?scope=all')->json('data'))->pluck('id'))->not->toContain($created['id']);

    $global = app(WorkoutBuilder::class)->create(Fixtures::workoutPayload($this->ex, 'Global'), null, null, true);
    $this->getJson('/api/v1/workouts/'.$global->id)->assertOk();
    expectApiError($this->putJson('/api/v1/workouts/'.$global->id, ['name' => 'x']), 403, 'forbidden');
    expect(Workout::withoutGlobalScopes()->find($global->id)->name)->toBe('Global');
});

afterEach(fn () => Model::preventLazyLoading(false));
