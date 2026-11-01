<?php

use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\NutritionTarget;
use Illuminate\Support\Str;

require_once __DIR__.'/NutritionTestHelpers.php';

beforeEach(function () {
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner);
    $this->rice = nutritionFood('Rice', 130, 2.7, 28.2, 0.3, ['servings' => [['unit' => 'cup', 'grams' => 158]]]);
    $this->chicken = nutritionFood('Chicken', 165, 31, 0, 3.6);
    $this->date = '2026-11-01';
});

function activePlanFor($test, array $items): array
{
    return $test->actingAsIn($test->owner, $test->tenant)->postJson('/api/v1/meal-plans', nutritionPlanPayload($items, [
        'status' => 'active', 'client_id' => $test->client->id, 'start_date' => '2026-10-01',
    ]))->assertCreated()->json('data');
}

it('REGRESSION: any food added to the active plan always appears in the client day', function () {
    $plan = activePlanFor($this, [['food_id' => $this->rice->id, 'quantity' => 1, 'unit' => 'cup']]);

    // Coach adds a brand-new custom (even pending-moderation) food to the plan, plus a soft-deleted one.
    $custom = $this->inTenant($this->tenant, fn () => Food::create(['name' => ['en' => 'Coach special'], 'kcal' => 200, 'protein_g' => 20, 'moderation' => 'pending']));
    $retired = nutritionFood('Retired food', 50, 1, 10, 0);
    $days = $plan['days'];
    $days[0]['meals'][0]['items'] = [
        ['food_id' => $this->rice->id, 'quantity' => 1, 'unit' => 'cup'],
        ['food_id' => $custom->id, 'quantity' => 150],
        ['food_id' => $retired->id, 'quantity' => 100],
    ];
    $days[0]['meals'][] = ['slot' => 'custom', 'name' => 'Pre-workout', 'items' => [['food_id' => $this->chicken->id, 'quantity' => 120]]];
    $this->patchJson("/api/v1/meal-plans/{$plan['id']}", ['days' => $days])->assertOk();
    $retired->delete();

    $this->actingAsIn($this->client, $this->tenant);
    foreach (['2026-11-01', '2026-11-02', '2026-12-25'] as $date) {
        $day = $this->getJson("/api/v1/me/nutrition?date={$date}")->assertOk();
        $planned = collect($day->json('data.meals'))->flatMap(fn ($m) => $m['planned']);
        expect($planned->pluck('food_id')->all())->toContain($this->rice->id, $custom->id, $retired->id, $this->chicken->id);
        $special = $planned->firstWhere('food_id', $custom->id);
        expect($special['name'])->toBe('Coach special')->and($special['kcal'])->toEqual(300.0)->and($special['logged'])->toBeFalse();
    }
    $pre = collect($this->getJson('/api/v1/me/nutrition?date=2026-11-01')->json('data.meals'))->firstWhere('slot', 'custom');
    expect($pre['label'])->toBe('Pre-workout');

    // The client plan endpoint shows them too.
    $items = collect($this->getJson('/api/v1/me/nutrition/plan')->json('data.days.0.meals'))->flatMap(fn ($m) => $m['items']);
    expect($items->pluck('food_id')->all())->toContain($custom->id, $retired->id);
});

it('logs food idempotently and computes macros server-side', function () {
    $this->actingAsIn($this->client, $this->tenant);
    $id = strtolower((string) Str::ulid());
    $payload = ['id' => $id, 'log_date' => $this->date, 'slot' => 'lunch', 'food_id' => $this->rice->id, 'quantity' => 2, 'unit' => 'cup', 'kcal' => 1, 'source' => 'search'];

    $first = $this->postJson('/api/v1/me/food-logs', $payload)->assertCreated();
    expect($first->json('data.kcal'))->toEqual(410.8)   // 316 g × 1.30
        ->and($first->json('data.carbs_g'))->toEqual(89.1)
        ->and($first->json('data.name'))->toBe('Rice');

    $this->postJson('/api/v1/me/food-logs', $payload)->assertOk()->assertJsonPath('data.id', $id);
    expect(FoodLog::withoutGlobalScopes()->count())->toBe(1);

    expectApiError($this->postJson('/api/v1/me/food-logs', ['log_date' => $this->date, 'slot' => 'lunch', 'food_id' => $this->rice->id, 'quantity' => 1, 'unit' => 'bowl']), 422, 'invalid_unit');

    $quick = $this->postJson('/api/v1/me/food-logs', ['log_date' => $this->date, 'slot' => 'snack', 'name' => 'Karak', 'quantity' => 1, 'unit' => 'serving', 'kcal' => 140, 'protein_g' => 4, 'source' => 'quick'])->assertCreated();
    expect($quick->json('data.name'))->toBe('Karak')->and($quick->json('data.kcal'))->toEqual(140.0);

    $day = $this->getJson("/api/v1/me/nutrition?date={$this->date}")->assertOk();
    expect($day->json('data.consumed.kcal'))->toEqual(550.8)
        ->and(collect($day->json('data.meals'))->firstWhere('slot', 'lunch')['logs'][0]['id'])->toBe($id);
});

it('logs a whole planned meal in one tap and flags planned items as logged', function () {
    activePlanFor($this, [['food_id' => $this->rice->id, 'quantity' => 1, 'unit' => 'cup'], ['food_id' => $this->chicken->id, 'quantity' => 150]]);
    $this->actingAsIn($this->client, $this->tenant);

    $logs = $this->postJson('/api/v1/me/food-logs/from-plan', ['log_date' => $this->date, 'slot' => 'breakfast'])->assertOk()->json('data.logs');
    expect($logs)->toHaveCount(2)->and(collect($logs)->pluck('source')->unique()->all())->toBe(['plan']);

    // Repeating is a no-op.
    $this->postJson('/api/v1/me/food-logs/from-plan', ['log_date' => $this->date, 'slot' => 'breakfast'])->assertOk();
    expect(FoodLog::withoutGlobalScopes()->count())->toBe(2);

    $breakfast = collect($this->getJson("/api/v1/me/nutrition?date={$this->date}")->json('data.meals'))->firstWhere('slot', 'breakfast');
    expect(collect($breakfast['planned'])->every(fn ($p) => $p['logged'] === true))->toBeTrue()
        ->and($breakfast['consumed']['kcal'])->toEqual(453.9);
});

it('copies a day, tracks water, recents, favourites and weekly adherence', function () {
    $this->actingAsIn($this->owner, $this->tenant)->postJson("/api/v1/clients/{$this->client->id}/nutrition-targets", ['kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67, 'water_ml' => 3000, 'effective_from' => '2026-10-01'])->assertCreated();

    $this->actingAsIn($this->client, $this->tenant);
    $this->postJson('/api/v1/me/food-logs', ['log_date' => '2026-10-31', 'slot' => 'lunch', 'food_id' => $this->rice->id, 'quantity' => 1400])->assertCreated(); // 1820 kcal
    $copied = $this->postJson('/api/v1/me/food-logs/copy-day', ['from_date' => '2026-10-31', 'to_date' => $this->date])->assertCreated();
    expect($copied->json('data.logs.0.source'))->toBe('copy');

    $waterId = strtolower((string) Str::ulid());
    $this->postJson('/api/v1/me/water', ['id' => $waterId, 'log_date' => $this->date, 'ml' => 500])->assertOk()->assertJsonPath('data.water_ml', 500)->assertJsonPath('data.water_target_ml', 3000);
    $this->postJson('/api/v1/me/water', ['id' => $waterId, 'log_date' => $this->date, 'ml' => 500])->assertOk()->assertJsonPath('data.water_ml', 500);
    $this->postJson('/api/v1/me/water', ['log_date' => $this->date, 'ml' => 250])->assertOk()->assertJsonPath('data.water_ml', 750);

    expect(collect($this->getJson('/api/v1/me/foods/recent')->json('data'))->pluck('id')->all())->toBe([$this->rice->id]);
    $this->postJson("/api/v1/me/foods/{$this->rice->id}/favorite")->assertOk()->assertJsonPath('data.is_favorite', true);
    $this->getJson("/api/v1/foods/{$this->rice->id}")->assertJsonPath('data.is_favorite', true);
    expect($this->getJson('/api/v1/me/foods/recent?favorites=1')->json('data.0.id'))->toBe($this->rice->id);
    $this->postJson("/api/v1/me/foods/{$this->rice->id}/favorite")->assertJsonPath('data.is_favorite', false);

    $week = $this->getJson("/api/v1/me/nutrition/week?date={$this->date}")->assertOk();
    expect($week->json('data.logged_days'))->toBe(2)
        ->and($week->json('data.adherence_pct'))->toBe(29) // 2 of 7 days within ±10 % of 2000 kcal
        ->and($week->json('data.averages.kcal'))->toEqual(1820.0);

    $day = $this->getJson("/api/v1/me/nutrition?date={$this->date}")->assertOk();
    expect($day->json('data.targets'))->toEqual(['kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67]);
});

it('never exposes another client\'s logs and isolates tenants', function () {
    $other = $this->addClient($this->tenant, $this->owner);
    $log = $this->inTenant($this->tenant, fn () => FoodLog::create(['client_id' => $other->id, 'log_date' => $this->date, 'slot' => 'lunch', 'name' => 'Private', 'kcal' => 500, 'logged_at' => now()]));

    $this->actingAsIn($this->client, $this->tenant);
    expect($this->getJson("/api/v1/me/nutrition?date={$this->date}")->json('data.consumed.kcal'))->toEqual(0.0);
    expectApiError($this->deleteJson("/api/v1/me/food-logs/{$log->id}"), 404, 'not_found');
    expectApiError($this->postJson('/api/v1/me/food-logs', ['id' => $log->id, 'log_date' => $this->date, 'slot' => 'lunch', 'food_id' => $this->rice->id, 'quantity' => 10]), 409, 'id_conflict');

    // A tenant's custom food is invisible to another tenant.
    $secret = $this->inTenant($this->tenant, fn () => Food::create(['name' => ['en' => 'Tenant A secret sauce'], 'kcal' => 100]));
    [$tenantB, $ownerB] = $this->createCoachTenant();
    $clientB = $this->addClient($tenantB, $ownerB);
    $this->actingAsIn($clientB, $tenantB);
    expect($this->getJson('/api/v1/foods?q=secret')->json('data'))->toBeEmpty();
    expectApiError($this->getJson("/api/v1/foods/{$secret->id}"), 404, 'not_found');
    expectApiError($this->actingAsIn($clientB, $this->tenant)->getJson('/api/v1/me/nutrition'), 403, 'forbidden');
});

it('lets clients delete their own logs', function () {
    $this->actingAsIn($this->client, $this->tenant);
    $id = $this->postJson('/api/v1/me/food-logs', ['log_date' => $this->date, 'slot' => 'dinner', 'food_id' => $this->chicken->id, 'quantity' => 100])->json('data.id');
    $this->deleteJson("/api/v1/me/food-logs/{$id}")->assertOk();
    expect(FoodLog::withoutGlobalScopes()->whereKey($id)->exists())->toBeFalse();
});

it('stores targets with safety floors and lets coaches override', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    expectApiError($this->postJson("/api/v1/clients/{$this->client->id}/nutrition-targets", ['kcal' => 1000, 'protein_g' => 100, 'carbs_g' => 80, 'fat_g' => 30]), 422, 'below_safety_floor');
    $this->postJson("/api/v1/clients/{$this->client->id}/nutrition-targets", ['kcal' => 1000, 'protein_g' => 100, 'carbs_g' => 80, 'fat_g' => 30, 'override_safety' => true])
        ->assertCreated()->assertJsonPath('data.0.override_safety', true);

    $calc = $this->postJson("/api/v1/clients/{$this->client->id}/nutrition-targets", [
        'calculate' => ['sex' => 'male', 'age' => 30, 'height_cm' => 180, 'weight_kg' => 80, 'activity' => 'moderate', 'goal' => 'fat_loss'],
        'split_day_types' => true,
        'effective_from' => '2026-11-01',
    ])->assertCreated();
    expect(collect($calc->json('data'))->pluck('day_type')->all())->toBe(['all', 'training', 'rest'])
        ->and($calc->json('data.0.kcal'))->toBe(2207)
        ->and($calc->json('data.0.calculation.result.formula'))->toBe('mifflin_st_jeor');

    $this->getJson("/api/v1/clients/{$this->client->id}/nutrition-targets")->assertOk()->assertJsonPath('meta.current.day_type', 'rest');

    $preview = $this->postJson('/api/v1/nutrition/calculate', ['sex' => 'female', 'age' => 25, 'height_cm' => 165, 'weight_kg' => 60, 'activity' => 'light'])->assertOk();
    expect($preview->json('data.bmr'))->toEqual(1345)->and($preview->json('data.tdee'))->toEqual(1850);
    expect(NutritionTarget::withoutGlobalScopes()->count())->toBe(4);

    $assistant = $this->addStaff($this->tenant, 'assistant');
    expectApiError($this->actingAsIn($assistant, $this->tenant)->postJson("/api/v1/clients/{$this->client->id}/nutrition-targets", ['kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67]), 403, 'forbidden');

    [$tenantB, $ownerB] = $this->createCoachTenant();
    expectApiError($this->actingAsIn($ownerB, $tenantB)->getJson("/api/v1/clients/{$this->client->id}/nutrition-targets"), 404, 'not_found');
});
