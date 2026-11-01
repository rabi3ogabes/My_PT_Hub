<?php

use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use Carbon\CarbonImmutable;
use Database\Seeders\Modules\HabitSeeder;

beforeEach(function () {
    $this->seed(HabitSeeder::class);
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner, ['timezone' => 'Asia/Qatar']);
});

afterEach(fn () => $this->travelBack());

it('seeds global habit templates in four languages', function () {
    $habits = Habit::withoutGlobalScopes()->whereNull('tenant_id')->get();
    expect($habits->pluck('key')->sort()->values()->all())->toBe(['meditation', 'no_sugar', 'protein', 'reading', 'sleep', 'steps', 'stretching', 'sunlight', 'veggies', 'water']);
    $habits->each(fn ($h) => expect(array_keys($h->name))->toBe(['en', 'ar', 'fr', 'es']));

    $list = $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/habits?lang=ar')->assertOk();
    expect(collect($list->json('data'))->pluck('name'))->toContain('اشرب الماء');
});

it('assigns habits, logs them and returns streaks for the client day', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-04 10:00:00'));
    $this->actingAsIn($this->owner, $this->tenant);
    $water = Habit::withoutGlobalScopes()->where('key', 'water')->first();
    $custom = $this->postJson('/api/v1/habits', ['name' => ['en' => 'Walk after dinner', 'ar' => 'المشي بعد العشاء'], 'type' => 'boolean', 'icon' => 'walk'])->assertCreated();

    $a = $this->postJson('/api/v1/habit-assignments', ['habit_id' => $water->id, 'client_ids' => [$this->client->id], 'schedule' => 'daily', 'start_date' => '2026-11-01', 'reminder_times' => ['08:00']])->assertCreated()->json('data.0');
    $b = $this->postJson('/api/v1/habit-assignments', ['habit_id' => $custom->json('data.id'), 'client_id' => $this->client->id, 'schedule' => 'days', 'days' => [1, 3], 'start_date' => '2026-11-01'])->assertCreated()->json('data.0');

    $this->actingAsIn($this->client, $this->tenant);
    foreach (['2026-11-01', '2026-11-02', '2026-11-03'] as $d) {
        $this->postJson("/api/v1/me/habits/{$a['id']}/log", ['date' => $d, 'value' => 2600])->assertOk();
    }
    $partial = $this->postJson("/api/v1/me/habits/{$a['id']}/log", ['date' => '2026-11-04', 'value' => 1000])->assertOk();
    expect($partial->json('data.completed'))->toBeFalse()
        ->and($partial->json('data.streak'))->toBe(['current' => 3, 'best' => 3])
        ->and($partial->json('data.target'))->toEqual(2500.0);

    $done = $this->postJson("/api/v1/me/habits/{$a['id']}/log", ['date' => '2026-11-04', 'value' => 2500])->assertOk();
    expect($done->json('data.completed'))->toBeTrue()->and($done->json('data.streak.current'))->toBe(4)->and($done->json('data.completion_pct_30d'))->toBe(100);

    // 2026-11-04 is a Wednesday (scheduled), 11-05 a Thursday (not scheduled).
    $today = collect($this->getJson('/api/v1/me/habits?date=2026-11-04')->assertOk()->json('data'))->keyBy('assignment_id');
    expect($today[$b['id']]['scheduled_today'])->toBeTrue()
        ->and($today[$a['id']]['reminder_times'])->toBe(['08:00'])
        ->and($today[$a['id']]['habit']['type'])->toBe('numeric');
    $thursday = collect($this->getJson('/api/v1/me/habits?date=2026-11-05')->json('data'))->keyBy('assignment_id');
    expect($thursday[$b['id']]['scheduled_today'])->toBeFalse();

    expectApiError($this->postJson("/api/v1/me/habits/{$a['id']}/log", ['date' => '2026-11-20']), 422, 'validation_failed');
});

it('uses the client timezone to decide today', function () {
    // 22:30 UTC on 11-03 is already 11-04 in Doha.
    $this->travelTo(CarbonImmutable::parse('2026-11-03 22:30:00'));
    $a = $this->inTenant($this->tenant, fn () => HabitAssignment::create([
        'habit_id' => Habit::withoutGlobalScopes()->where('key', 'protein')->value('id'),
        'client_id' => $this->client->id, 'schedule' => 'daily', 'start_date' => '2026-11-04', 'is_active' => true,
    ]));
    $this->actingAsIn($this->client, $this->tenant);
    $this->postJson("/api/v1/me/habits/{$a->id}/log", ['date' => '2026-11-04'])->assertOk()->assertJsonPath('data.completed', true);
    expect($this->getJson('/api/v1/me/habits')->json('data.0.completed'))->toBeTrue();
});

it('updates and unassigns while keeping history, and isolates clients and tenants', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $habit = Habit::withoutGlobalScopes()->where('key', 'steps')->first();
    $a = $this->postJson('/api/v1/habit-assignments', ['habit_id' => $habit->id, 'client_ids' => [$this->client->id], 'schedule' => 'per_week', 'per_week' => 4])->json('data.0');
    $this->patchJson("/api/v1/habit-assignments/{$a['id']}", ['target' => 10000])->assertOk()->assertJsonPath('data.target', 10000)->assertJsonPath('data.per_week', 4);

    $other = $this->addClient($this->tenant, $this->owner);
    expectApiError($this->actingAsIn($other, $this->tenant)->postJson("/api/v1/me/habits/{$a['id']}/log", ['date' => now()->toDateString()]), 404, 'not_found');

    [$tenantB, $ownerB] = $this->createCoachTenant();
    expectApiError($this->actingAsIn($ownerB, $tenantB)->patchJson("/api/v1/habit-assignments/{$a['id']}", ['target' => 1]), 404, 'not_found');
    expectApiError($this->actingAsIn($ownerB, $tenantB)->postJson('/api/v1/habit-assignments', ['habit_id' => $habit->id, 'client_ids' => [$this->client->id], 'schedule' => 'daily']), 422, 'validation_failed');

    $this->actingAsIn($this->owner, $this->tenant)->deleteJson("/api/v1/habit-assignments/{$a['id']}")->assertOk();
    expect(HabitAssignment::withoutGlobalScopes()->find($a['id'])->is_active)->toBeFalse();
    expect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/habits')->json('data'))->toBeEmpty();
});
