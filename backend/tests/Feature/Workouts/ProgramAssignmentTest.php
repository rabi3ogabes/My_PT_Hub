<?php

use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Nutrition\Models\WaterLog;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Services\ProgramAssigner;
use App\Domain\Workouts\Services\ProgramBuilder;
use App\Domain\Workouts\Services\ProgramSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Tests\Feature\Workouts\Fixtures;

beforeEach(function () {
    Model::preventLazyLoading(); // catch N+1 / lazy loads (dev behaviour)
    Carbon::setTestNow('2026-11-02 08:00:00'); // Monday
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner, ['timezone' => 'UTC', 'name' => 'Omar Haddad']);
    $this->ex = Fixtures::library();
    [$this->workout, $this->program] = $this->inTenant($this->tenant, function () {
        $w = Fixtures::workout($this->ex, $this->owner->id);

        return [$w, Fixtures::program($w)];
    });
});

afterEach(function () {
    Carbon::setTestNow();
    Model::preventLazyLoading(false);
});

it('creates programs with nested weeks/days, copies a week and duplicates', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $res = $this->postJson('/api/v1/programs', [
        'name' => ['en' => 'Hypertrophy 4', 'ar' => 'تضخيم ٤'],
        'level' => 'intermediate', 'days_per_week' => 3,
        'weeks' => [
            ['phase' => 'hypertrophy', 'days' => [['day_number' => 1, 'workout_id' => $this->workout->id], ['day_number' => 4, 'workout_id' => $this->workout->id]]],
            ['phase' => 'deload', 'is_deload' => true, 'days' => [['day_number' => 1, 'workout_id' => $this->workout->id]]],
        ],
    ])->assertCreated();
    $program = $res->json('data');
    expect($program['weeks_count'])->toBe(2)
        ->and($program['weeks'][0]['days'][1]['day_number'])->toBe(4)
        ->and($program['weeks'][0]['days'][0]['workout']['name'])->toBe('Push Day')
        ->and($program['name_translations']['ar'])->toBe('تضخيم ٤');

    $copied = $this->postJson("/api/v1/programs/{$program['id']}/weeks/{$program['weeks'][0]['id']}/copy")->assertOk()->json('data');
    expect($copied['weeks_count'])->toBe(3)->and(count($copied['weeks'][2]['days']))->toBe(2)->and($copied['weeks'][2]['phase'])->toBe('hypertrophy');

    // Update keeps existing day ids (sessions reference them).
    $dayId = $copied['weeks'][0]['days'][0]['id'];
    $updated = $this->putJson("/api/v1/programs/{$program['id']}", ['weeks' => [
        ['id' => $copied['weeks'][0]['id'], 'days' => [['id' => $dayId, 'day_number' => 2, 'workout_id' => $this->workout->id]]],
    ]])->assertOk()->json('data');
    expect($updated['weeks_count'])->toBe(1)->and($updated['weeks'][0]['days'][0]['id'])->toBe($dayId)->and($updated['weeks'][0]['days'][0]['day_number'])->toBe(2);

    $dup = $this->postJson("/api/v1/programs/{$program['id']}/duplicate")->assertCreated()->json('data');
    expect($dup['id'])->not->toBe($program['id'])->and($dup['name'])->toBe('Hypertrophy 4 (copy)');
});

it('assigns a program to many clients and computes today, unlock modes, pause and shift', function () {
    $client2 = $this->addClient($this->tenant, $this->owner);
    $this->actingAsIn($this->owner, $this->tenant);

    $res = $this->postJson("/api/v1/programs/{$this->program->id}/assign", [
        'client_ids' => [$this->client->id, $client2->id], 'start_date' => '2026-11-02', 'unlock_mode' => 'weekly_drip',
    ])->assertCreated();
    expect($res->json('data'))->toHaveCount(2)
        ->and($res->json('data.0'))->toHaveKeys(['id', 'program', 'client', 'start_date', 'status', 'unlock_mode', 'current_week', 'progress_pct'])
        ->and($res->json('data.0.status'))->toBe('active')
        ->and($res->json('data.0.current_week'))->toBe(1);
    $assignmentId = $res->json('data.0.id');

    // Day 1 (Monday) → workout; client view.
    $today = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/today')->assertOk();
    expect($today->json('data.workout'))->toMatchArray([
        'workout_id' => $this->workout->id, 'program_assignment_id' => $assignmentId,
        'week_number' => 1, 'day_number' => 1, 'status' => 'pending', 'session_id' => null, 'name' => 'Push', 'exercise_count' => 5,
    ])->and($today->json('data.greeting_name'))->toBe('Omar')
        ->and($today->json('data.coach.id'))->toBe($this->owner->id);

    // Day 2 is a rest day; day 8 = week 2 day 1.
    expect($this->getJson('/api/v1/me/today?date=2026-11-03')->json('data.workout'))->toBeNull();
    expect($this->getJson('/api/v1/me/today?date=2026-11-09')->json('data.workout.week_number'))->toBe(2);
    // After the program ends → nothing.
    expect($this->getJson('/api/v1/me/today?date=2026-11-20')->json('data.workout'))->toBeNull();

    $program = $this->getJson('/api/v1/me/program')->assertOk();
    expect($program->json('data.unlocked_through'))->toBe('2026-11-08') // weekly drip: end of week 1
        ->and($program->json('data.program.weeks_count'))->toBe(2);

    // Coach shifts the schedule by 2 days → today is before day 1.
    $this->actingAsIn($this->owner, $this->tenant)->patchJson("/api/v1/assignments/{$assignmentId}", ['shift_by' => 2])->assertOk()->assertJsonPath('data.shift_days', 2);
    expect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/today')->json('data.workout'))->toBeNull();
    expect($this->getJson('/api/v1/me/today?date=2026-11-04')->json('data.workout.day_number'))->toBe(1);

    // Pause on Nov 4 (day 1), resume Nov 7 → shift grows by 3, schedule continues at day 1 on Nov 7.
    Carbon::setTestNow('2026-11-04 09:00:00');
    $this->actingAsIn($this->owner, $this->tenant)->patchJson("/api/v1/assignments/{$assignmentId}", ['action' => 'pause'])->assertOk()->assertJsonPath('data.status', 'paused');
    Carbon::setTestNow('2026-11-07 09:00:00');
    $this->patchJson("/api/v1/assignments/{$assignmentId}", ['action' => 'resume'])->assertOk()
        ->assertJsonPath('data.status', 'active')->assertJsonPath('data.shift_days', 5);
    expect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/today')->json('data.workout.day_number'))->toBe(1);

    $list = $this->actingAsIn($this->owner, $this->tenant)->getJson("/api/v1/assignments?client_id={$this->client->id}")->assertOk();
    expect($list->json('data'))->toHaveCount(1);
});

it('computes schedule positions per the shared formula', function () {
    $assignment = $this->inTenant($this->tenant, fn () => app(ProgramAssigner::class)->assign($this->program, $this->client, '2026-11-02', null, 'daily'));
    $schedule = app(ProgramSchedule::class);

    expect($schedule->position($assignment, '2026-11-01'))->toBeNull()
        ->and($schedule->position($assignment, '2026-11-02'))->toBe(['week_number' => 1, 'day_number' => 1])
        ->and($schedule->position($assignment, '2026-11-12'))->toBe(['week_number' => 2, 'day_number' => 4])
        ->and($schedule->position($assignment, '2026-11-16'))->toBeNull()
        ->and($schedule->dateFor($assignment, 2, 3)->toDateString())->toBe('2026-11-11')
        ->and($schedule->unlockedThrough($assignment, CarbonImmutable::parse('2026-11-04'))->toDateString())->toBe('2026-11-04');

    $assignment->forceFill(['shift_days' => 3])->save();
    expect($schedule->dateFor($assignment, 1, 1)->toDateString())->toBe('2026-11-05')
        ->and($schedule->position($assignment, '2026-11-05'))->toBe(['week_number' => 1, 'day_number' => 1]);

    $assignment->forceFill(['status' => 'paused', 'paused_at' => '2026-11-06'])->save();
    expect($schedule->position($assignment, '2026-11-30'))->toBe(['week_number' => 1, 'day_number' => 2]);
});

it('assigns by id for other modules and replaces the previous active program idempotently', function () {
    $assigner = app(ProgramAssigner::class);
    $first = $this->inTenant($this->tenant, fn () => $assigner->assignById($this->program->id, $this->client, '2026-11-02', $this->owner->id));
    $again = $this->inTenant($this->tenant, fn () => $assigner->assignById($this->program->id, $this->client, '2026-11-02', $this->owner->id));
    expect($again->id)->toBe($first->id);

    $other = $this->inTenant($this->tenant, fn () => Fixtures::program($this->workout, 1));
    $second = $this->inTenant($this->tenant, fn () => $assigner->assign($other, $this->client, '2026-11-10'));
    expect($second->status)->toBe('scheduled')
        ->and(ProgramAssignment::withoutGlobalScopes()->find($first->id)->status)->toBe('cancelled');
});

it('fills the Today payload from nutrition, habits and progress tables', function () {
    $this->inTenant($this->tenant, function () {
        NutritionTarget::create(['client_id' => $this->client->id, 'kcal' => 2200, 'protein_g' => 160, 'carbs_g' => 220, 'fat_g' => 70, 'water_ml' => 3000, 'effective_from' => '2026-10-01']);
        FoodLog::create(['client_id' => $this->client->id, 'log_date' => '2026-11-02', 'slot' => 'breakfast', 'name' => 'Oats', 'quantity' => 1, 'kcal' => 400, 'protein_g' => 20, 'carbs_g' => 60, 'fat_g' => 8, 'logged_at' => now()]);
        FoodLog::create(['client_id' => $this->client->id, 'log_date' => '2026-11-01', 'slot' => 'lunch', 'name' => 'Rice', 'quantity' => 1, 'kcal' => 500, 'logged_at' => now()->subDay()]);
        WaterLog::create(['client_id' => $this->client->id, 'log_date' => '2026-11-02', 'ml' => 750, 'logged_at' => now()]);
        $habit = Habit::create(['tenant_id' => null, 'name' => ['en' => 'Steps'], 'type' => 'count', 'target' => 8000]);
        $a1 = HabitAssignment::create(['habit_id' => $habit->id, 'client_id' => $this->client->id, 'start_date' => '2026-10-01']);
        HabitAssignment::create(['habit_id' => $habit->id, 'client_id' => $this->client->id, 'start_date' => '2026-10-01']);
        HabitLog::create(['habit_assignment_id' => $a1->id, 'log_date' => '2026-11-02', 'value' => 9000, 'completed' => true]);
        HabitLog::create(['habit_assignment_id' => $a1->id, 'log_date' => '2026-10-31', 'value' => 9000, 'completed' => true]);
    });

    $data = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/today')->assertOk()->json('data');
    expect($data['nutrition']['targets'])->toEqual(['kcal' => 2200.0, 'protein_g' => 160.0, 'carbs_g' => 220.0, 'fat_g' => 70.0])
        ->and($data['nutrition']['consumed']['kcal'])->toEqual(400)
        ->and($data['nutrition']['water_ml'])->toBe(750)
        ->and($data['nutrition']['water_target_ml'])->toBe(3000)
        ->and($data['habits'])->toBe(['completed' => 1, 'total' => 2])
        ->and($data['streak'])->toBe(['current' => 3, 'best' => 3])
        ->and($data['checkin_due'])->toBeNull()
        ->and($data['unread_messages'])->toBe(0)
        ->and($data['next_session'])->toBeNull();
});

it('prevents tenant B from reading tenant A programs and assignments', function () {
    $assignment = $this->inTenant($this->tenant, fn () => app(ProgramAssigner::class)->assign($this->program, $this->client, '2026-11-02'));
    [$tenantB, $ownerB] = $this->createCoachTenant();
    $this->actingAsIn($ownerB, $tenantB);

    expectApiError($this->getJson("/api/v1/programs/{$this->program->id}"), 404, 'not_found');
    expectApiError($this->postJson("/api/v1/programs/{$this->program->id}/assign", ['client_ids' => [$this->client->id]]), 404, 'not_found');
    expectApiError($this->patchJson("/api/v1/assignments/{$assignment->id}", ['action' => 'pause']), 404, 'not_found');
    expect($this->getJson('/api/v1/assignments')->json('data'))->toBeEmpty();
    expect(ProgramDay::count())->toBeGreaterThan(0);
});

it('lists platform public templates without authentication', function () {
    $this->inTenant($this->tenant, fn () => null);
    $template = app(ProgramBuilder::class)->create([
        'name' => ['en' => 'Public Full Body', 'ar' => 'الجسم كامل'], 'visibility' => 'public', 'is_template' => true, 'weeks' => [],
    ], null, true);

    $res = $this->getJson('/api/v1/public/program-templates')->assertOk();
    expect(collect($res->json('data'))->pluck('id')->all())->toBe([$template->id]);
});
