<?php

use App\Domain\Ai\Models\AiRequest;
use App\Domain\Ai\Providers\Fake\FakeResponder;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\MealItem;
use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Nutrition\Models\NutritionTarget;
use Tests\Feature\Ai\Support\AiFixtures;

uses(AiFixtures::class);

beforeEach(function () {
    $this->foods = $this->seedFoods();
    [$this->tenant, $this->coach] = $this->proTenant();
});

/** Recomputes a day's totals from the DB (independent of the generator's own numbers). */
function dayTotalsFromDb(array $day): array
{
    $totals = ['kcal' => 0.0, 'protein_g' => 0.0, 'carbs_g' => 0.0, 'fat_g' => 0.0];
    foreach ($day['meals'] as $meal) {
        foreach ($meal['items'] as $item) {
            $food = Food::withoutGlobalScopes()->findOrFail($item['food_id']);
            foreach (array_keys($totals) as $m) {
                $totals[$m] += $food->{$m} * $item['quantity'] / 100;
            }
        }
    }

    return $totals;
}

it('builds a Gulf meal plan in Arabic whose macros are verified within ±5%', function () {
    $targets = ['kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67];

    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/meal-plans', $targets + [
        'cuisine' => 'gulf', 'days' => 3, 'meals_per_day' => 4, 'language' => 'ar', 'diet_tags' => ['halal'],
    ])->assertCreated();

    $payload = $response->json('data.payload');
    expect($payload['days'])->toHaveCount(3)->and($payload['language'])->toBe('ar');
    foreach ($payload['days'] as $day) {
        $totals = dayTotalsFromDb($day);
        foreach ($targets as $metric => $target) {
            expect(abs($totals[$metric] - $target) / $target)->toBeLessThanOrEqual(0.05, "{$metric} day {$day['day_number']}");
        }
        expect($day['meals'][0]['name'])->toBe('الفطور');
    }
    expect($payload['grocery_list'])->not->toBeEmpty()
        ->and(MealPlan::withoutGlobalScopes()->count())->toBe(0);
});

it('scales out-of-range portions deterministically and regenerates when a day cannot be fixed', function () {
    // First answer: a day with only rice (cannot reach protein/fat even after adding sources? it can) - force an unknown id instead.
    $this->fake()->push('meal_plan', function ($prompt) {
        $data = app(FakeResponder::class)->structured('meal_plan', $prompt->context);
        $data['days'][0]['meals'][0]['items'][0]['food_id'] = 'unknown-food';

        return $data;
    });
    $this->fake()->push('meal_plan', function ($prompt) {
        $data = app(FakeResponder::class)->structured('meal_plan', $prompt->context);
        // Wildly wrong portions: the balancer must fix them.
        foreach ($data['days'] as &$day) {
            foreach ($day['meals'] as &$meal) {
                foreach ($meal['items'] as &$item) {
                    $item['quantity'] = 25;
                }
            }
        }

        return $data;
    });

    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/meal-plans', [
        'kcal' => 2400, 'protein_g' => 180, 'carbs_g' => 260, 'fat_g' => 70, 'days' => 2,
    ])->assertCreated();

    foreach ($response->json('data.payload.days') as $day) {
        foreach ($day['deviations'] as $metric => $dev) {
            expect(abs($dev))->toBeLessThanOrEqual(0.05, $metric);
        }
    }
    expect(AiRequest::query()->where('feature', 'meal_plan')->pluck('status')->all())->toBe(['invalid', 'ok']);
});

it('uses Ramadan slots (suhoor, iftar, snack)', function () {
    $payload = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/meal-plans', [
        'kcal' => 2200, 'protein_g' => 160, 'carbs_g' => 230, 'fat_g' => 72, 'days' => 1, 'ramadan' => true, 'cuisine' => 'gulf',
    ])->assertCreated()->json('data.payload');

    expect(collect($payload['days'][0]['meals'])->pluck('slot')->unique()->values()->all())->toBe(['suhoor', 'iftar', 'snack'])
        ->and($payload['ramadan'])->toBeTrue();
});

it('enforces the kcal floor for a female client without a coach override', function () {
    $client = $this->clientWithProfile($this->tenant, $this->coach, ['gender' => 'female']);

    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/meal-plans', [
        'client_id' => $client->id, 'kcal' => 1000, 'protein_g' => 90, 'carbs_g' => 90, 'fat_g' => 31, 'days' => 1,
    ])->assertCreated();

    expect($response->json('data.payload.targets.kcal'))->toBe(1200)
        ->and(collect($response->json('data.flags'))->pluck('type'))->toContain('kcal_floor_applied');
    $totals = dayTotalsFromDb($response->json('data.payload.days.0'));
    expect($totals['kcal'])->toBeGreaterThanOrEqual(1200 * 0.95);
});

it('respects a coach override stored on the client nutrition target', function () {
    $client = $this->clientWithProfile($this->tenant, $this->coach, ['gender' => 'female']);
    $this->inTenant($this->tenant, fn () => NutritionTarget::query()->create([
        'client_id' => $client->id, 'kcal' => 1100, 'protein_g' => 100, 'carbs_g' => 100, 'fat_g' => 33, 'effective_from' => now()->toDateString(), 'override_safety' => true,
    ]));

    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/meal-plans', ['client_id' => $client->id, 'days' => 1])->assertCreated();

    expect($response->json('data.payload.targets.kcal'))->toBe(1100);
});

it('honours diet tags and allergies', function () {
    $payload = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/meal-plans', [
        'kcal' => 2000, 'protein_g' => 120, 'carbs_g' => 240, 'fat_g' => 62, 'days' => 2, 'diet_tags' => ['vegan'], 'allergies' => ['nuts', 'soy'],
    ])->assertCreated()->json('data.payload');

    $ids = collect($payload['days'])->flatMap(fn ($d) => collect($d['meals'])->flatMap(fn ($m) => collect($m['items'])->pluck('food_id')))->unique();
    foreach (Food::withoutGlobalScopes()->whereIn('id', $ids)->get() as $food) {
        expect($food->diet_tags)->toContain('vegan')
            ->and(array_intersect($food->allergens ?? [], ['nuts', 'soy']))->toBe([]);
    }
});

it('rejects inconsistent targets and applies an approved plan', function () {
    $bad = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/meal-plans', ['kcal' => 2000, 'protein_g' => 300, 'carbs_g' => 300, 'fat_g' => 100]);
    expectApiError($bad, 422, 'targets_inconsistent');

    $client = $this->addClient($this->tenant, $this->coach);
    $id = $this->postJson('/api/v1/ai/meal-plans', ['client_id' => $client->id, 'kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67, 'days' => 2, 'name' => 'Cut phase'])
        ->assertCreated()->json('data.id');

    $this->postJson("/api/v1/ai/outputs/{$id}/approve", ['apply' => true])->assertOk()->assertJsonPath('data.status', 'applied');

    $plan = MealPlan::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->firstOrFail();
    expect($plan->client_id)->toBe($client->id)
        ->and($plan->name)->toBe('Cut phase')
        ->and($plan->status)->toBe('draft')
        ->and($plan->days()->count())->toBe(2)
        ->and(MealItem::query()->count())->toBeGreaterThan(6);
});

it('refuses to plan when the food library cannot cover protein, carbs and fat', function () {
    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/ai/meal-plans', [
        'kcal' => 2000, 'protein_g' => 120, 'carbs_g' => 240, 'fat_g' => 62, 'diet_tags' => ['vegan'], 'allergies' => ['nuts', 'soy', 'gluten'],
    ]);

    expectApiError($response, 422, 'food_library_empty');
    expect($this->fake()->callsFor('meal_plan'))->toBe([]);
});
