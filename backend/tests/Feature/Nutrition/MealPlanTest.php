<?php

use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Nutrition\Models\Recipe;

require_once __DIR__.'/NutritionTestHelpers.php';

beforeEach(function () {
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner);
    $this->oats = nutritionFood('Oats', 379, 13.2, 67.7, 6.5, ['category' => 'grains', 'servings' => [['unit' => 'cup', 'grams' => 81]]]);
    $this->egg = nutritionFood('Egg', 143, 12.6, 0.7, 9.5, ['category' => 'dairy', 'servings' => [['unit' => 'piece', 'grams' => 50]]]);
    $this->chicken = nutritionFood('Chicken breast', 165, 31, 0, 3.6, ['category' => 'meat', 'diet_tags' => ['halal']]);
    $this->turkey = nutritionFood('Turkey breast', 135, 30, 0, 1.0, ['category' => 'meat', 'diet_tags' => ['halal']]);
    $this->beef = nutritionFood('Fatty beef', 300, 20, 0, 24, ['category' => 'meat', 'diet_tags' => ['halal']]);
});

it('creates a nested plan and computes macro totals per meal and day', function () {
    $response = $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/meal-plans', nutritionPlanPayload([
        ['food_id' => $this->oats->id, 'quantity' => 1, 'unit' => 'cup'],      // 81 g → 307 kcal
        ['food_id' => $this->egg->id, 'quantity' => 2, 'unit' => 'piece'],     // 100 g → 143 kcal
        ['food_id' => $this->chicken->id, 'quantity' => 150],                  // 150 g → 247.5 kcal
    ]))->assertCreated();

    $meal = $response->json('data.days.0.meals.0');
    expect($meal['items'])->toHaveCount(3)
        ->and($meal['items'][0]['kcal'])->toEqual(307.0)
        ->and($meal['items'][1]['protein_g'])->toEqual(12.6)
        ->and($meal['totals']['kcal'])->toEqual(697.5)
        ->and($response->json('data.days.0.totals.protein_g'))->toEqual(70.0)
        ->and($response->json('data.daily_average.kcal'))->toEqual(697.5);
});

it('rejects unknown units and foods from another tenant', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    expectApiError($this->postJson('/api/v1/meal-plans', nutritionPlanPayload([['food_id' => $this->oats->id, 'quantity' => 1, 'unit' => 'bucket']])), 422, 'invalid_unit');

    [$other] = $this->createCoachTenant();
    $foreign = $this->inTenant($other, fn () => nutritionFood('Secret food', 100, 1, 1, 1, ['tenant_id' => $other->id]));
    expectApiError($this->postJson('/api/v1/meal-plans', nutritionPlanPayload([['food_id' => $foreign->id, 'quantity' => 100]])), 422, 'validation_failed');
});

it('assigns a copy to clients, builds a grocery list grouped by aisle and suggests swaps', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $plan = $this->postJson('/api/v1/meal-plans', nutritionPlanPayload([
        ['food_id' => $this->oats->id, 'quantity' => 80],
        ['food_id' => $this->chicken->id, 'quantity' => 200],
    ], ['is_template' => true]))->json('data');

    $assigned = $this->postJson("/api/v1/meal-plans/{$plan['id']}/assign", ['client_ids' => [$this->client->id], 'start_date' => '2026-11-01'])->assertCreated();
    expect($assigned->json('data.0.client.id'))->toBe($this->client->id)
        ->and($assigned->json('data.0.status'))->toBe('active')
        ->and($assigned->json('data.0.id'))->not->toBe($plan['id']);

    $grocery = $this->getJson("/api/v1/meal-plans/{$plan['id']}/grocery-list?repeat=2")->assertOk();
    $aisles = collect($grocery->json('data.aisles'))->keyBy('aisle');
    expect($aisles->keys()->all())->toBe(['meat', 'grains'])
        ->and($aisles['meat']['items'][0]['grams'])->toEqual(400)
        ->and($aisles['grains']['items'][0]['grams'])->toEqual(160);

    $itemId = $plan['days'][0]['meals'][0]['items'][1]['id'];
    $swaps = $this->postJson("/api/v1/meal-plans/{$plan['id']}/items/{$itemId}/swaps")->assertOk()->json('data');
    $names = collect($swaps)->pluck('food.name');
    expect($names)->toContain('Turkey breast')->not->toContain('Fatty beef');
    $turkey = collect($swaps)->firstWhere('food.name', 'Turkey breast');
    expect(abs($turkey['kcal_diff_pct']))->toBeLessThanOrEqual(10.0);

    $applied = $this->postJson("/api/v1/meal-plans/{$plan['id']}/items/{$itemId}/swaps", ['food_id' => $this->turkey->id, 'quantity' => $turkey['quantity']])->assertOk();
    expect($applied->json('data.days.0.meals.0.items.1.food_id'))->toBe($this->turkey->id);
});

it('computes recipe macros per serving and scales a recipe', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $recipe = $this->postJson('/api/v1/recipes', [
        'name' => ['en' => 'Egg & oat bowl', 'ar' => 'وعاء البيض والشوفان'],
        'servings' => 2,
        'steps' => ['Cook oats', 'Add eggs'],
        'items' => [
            ['food_id' => $this->oats->id, 'grams' => 100, 'display_qty' => 1.2, 'display_unit' => 'cup'],
            ['food_id' => $this->egg->id, 'grams' => 200, 'display_qty' => 4, 'display_unit' => 'piece'],
        ],
    ])->assertCreated();
    // (379 + 286) / 2 = 332.5 kcal; protein (13.2 + 25.2)/2 = 19.2
    $recipe->assertJsonPath('data.per_serving.kcal', 332.5)->assertJsonPath('data.per_serving.protein_g', 19.2);

    $scaled = $this->getJson('/api/v1/recipes/'.$recipe->json('data.id').'?servings=4')->assertOk();
    expect($scaled->json('data.servings'))->toBe(4)
        ->and($scaled->json('data.items.0.grams'))->toEqual(200.0)
        ->and($scaled->json('data.items.1.display_qty'))->toEqual(8.0)
        ->and($scaled->json('data.per_serving.kcal'))->toEqual(332.5);

    // Recipe in a plan: 1.5 servings.
    $plan = $this->postJson('/api/v1/meal-plans', nutritionPlanPayload([['recipe_id' => $recipe->json('data.id'), 'quantity' => 1.5, 'unit' => 'serving']]))->assertCreated();
    expect($plan->json('data.days.0.totals.kcal'))->toEqual(498.8);
});

it('protects global recipes from edits', function () {
    $global = Recipe::withoutGlobalScopes()->create(['tenant_id' => null, 'slug' => 'g', 'name' => ['en' => 'Global'], 'servings' => 1]);
    $this->actingAsIn($this->owner, $this->tenant);
    expectApiError($this->patchJson("/api/v1/recipes/{$global->id}", ['name' => 'Mine']), 403, 'forbidden');
});

it('renders a PDF of the plan including Arabic', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $plan = $this->postJson('/api/v1/meal-plans', nutritionPlanPayload([['food_id' => $this->oats->id, 'quantity' => 80]], ['name' => 'خطة التنشيف']))->json('data');

    $pdf = $this->get("/api/v1/meal-plans/{$plan['id']}/pdf?lang=ar")->assertOk();
    expect($pdf->headers->get('Content-Type'))->toBe('application/pdf')
        ->and(substr($pdf->getContent(), 0, 4))->toBe('%PDF');
});

it('shifts Ramadan meals to suhoor and iftar for the plan city', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $plan = $this->postJson('/api/v1/meal-plans', [
        'name' => 'Ramadan', 'type' => 'ramadan', 'status' => 'active', 'client_id' => $this->client->id, 'start_date' => '2026-02-18',
        'fasting' => ['city' => 'doha'],
        'days' => [['day_number' => 1, 'meals' => [
            ['slot' => 'breakfast', 'items' => [['food_id' => $this->oats->id, 'quantity' => 60]]],
            ['slot' => 'lunch', 'items' => [['food_id' => $this->chicken->id, 'quantity' => 150]]],
        ]]],
    ])->assertCreated();

    $this->actingAsIn($this->client, $this->tenant);
    $day = $this->getJson('/api/v1/me/nutrition?date=2026-03-01')->assertOk();
    expect($day->json('data.fasting.mode'))->toBe('ramadan');
    $iftar = $day->json('data.fasting.window_start');
    $suhoorEnd = $day->json('data.fasting.window_end');
    expect($iftar >= '17:00' && $iftar <= '18:30')->toBeTrue("iftar $iftar")
        ->and($suhoorEnd >= '04:00' && $suhoorEnd <= '05:00')->toBeTrue("suhoor $suhoorEnd");

    $meals = collect($day->json('data.meals'))->keyBy('slot');
    expect($meals['suhoor']['planned'][0]['food_id'])->toBe($this->oats->id)
        ->and($meals['iftar']['planned'][0]['food_id'])->toBe($this->chicken->id);

    $clientPlan = $this->getJson('/api/v1/me/nutrition/plan?date=2026-03-01')->assertOk();
    expect($clientPlan->json('data.days.0.meals.1.slot'))->toBe('iftar')
        ->and($clientPlan->json('data.days.0.meals.1.time'))->toBe($iftar);

    expectApiError($this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/meal-plans', ['name' => 'x', 'type' => 'ramadan', 'fasting' => ['city' => 'atlantis'], 'days' => []]), 422, 'unknown_city');
});

it('keeps meal plans tenant-isolated', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $plan = $this->postJson('/api/v1/meal-plans', nutritionPlanPayload([]))->json('data');

    [$otherTenant, $otherOwner] = $this->createCoachTenant();
    $this->actingAsIn($otherOwner, $otherTenant);
    expectApiError($this->getJson("/api/v1/meal-plans/{$plan['id']}"), 404, 'not_found');
    expect($this->getJson('/api/v1/meal-plans')->json('data'))->toBeEmpty();
    expect(MealPlan::withoutGlobalScopes()->count())->toBe(1);
});
