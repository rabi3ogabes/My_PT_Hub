<?php

use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\Recipe;
use Database\Seeders\Modules\FoodSeeder;
use Database\Seeders\Modules\RecipeSeeder;

beforeEach(function () {
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner);
});

it('seeds 40+ recipes with computed macros and steps in four languages', function () {
    $this->seed([FoodSeeder::class, RecipeSeeder::class]);
    $this->seed(RecipeSeeder::class); // idempotent

    $recipes = Recipe::withoutGlobalScopes()->whereNull('tenant_id')->with('items')->get();
    expect($recipes->count())->toBeGreaterThanOrEqual(40)
        ->and($recipes->pluck('cuisine')->unique()->sort()->values()->all())->toBe(['french', 'gulf', 'international', 'levant', 'maghreb']);
    $recipes->each(function (Recipe $r) {
        expect($r->kcal)->toBeGreaterThan(0)->and($r->items)->not->toBeEmpty();
        foreach (['en', 'ar', 'fr', 'es'] as $locale) {
            expect($r->steps[$locale] ?? [])->not->toBeEmpty()->and($r->name[$locale] ?? '')->not->toBe('');
        }
    });

    $this->actingAsIn($this->client, $this->tenant);
    $found = $this->getJson('/api/v1/recipes?lang=ar&q='.urlencode('كبسة'))->assertOk()->json('data');
    expect($found)->not->toBeEmpty();
    $detail = $this->getJson('/api/v1/recipes/'.$found[0]['id'].'?lang=ar')->assertOk();
    expect($detail->json('data.steps.0'))->toContain('شوّح')
        ->and($detail->json('data.items'))->not->toBeEmpty()
        ->and($detail->json('data.per_serving.kcal'))->toBeGreaterThan(200);
});

it('lets staff create custom foods; assistant submissions are moderated', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $food = $this->postJson('/api/v1/foods', [
        'name' => ['en' => 'Coach granola', 'ar' => 'غرانولا المدرب'],
        'region' => 'gulf', 'category' => 'grains',
        'per_100g' => ['kcal' => 420, 'protein_g' => 12, 'carbs_g' => 60, 'fat_g' => 14],
        'servings' => [['unit' => 'cup', 'grams' => 60]],
        'diet_tags' => ['vegetarian'],
    ])->assertCreated();
    $food->assertJsonPath('data.is_custom', true)->assertJsonPath('data.moderation', 'approved')->assertJsonPath('data.servings.0.label', 'cup');
    $this->patchJson('/api/v1/foods/'.$food->json('data.id'), ['per_100g' => ['kcal' => 410]])->assertOk()->assertJsonPath('data.per_100g.kcal', 410);

    $global = Food::withoutGlobalScopes()->create(['tenant_id' => null, 'name' => ['en' => 'Global apple'], 'kcal' => 52]);
    expectApiError($this->patchJson("/api/v1/foods/{$global->id}", ['brand' => 'x']), 403, 'forbidden');

    $assistant = $this->addStaff($this->tenant, 'assistant');
    $pending = $this->actingAsIn($assistant, $this->tenant)->postJson('/api/v1/foods', ['name' => 'Mystery bar', 'per_100g' => ['kcal' => 400]])->assertCreated();
    $pending->assertJsonPath('data.moderation', 'pending');

    // Clients don't see pending foods; staff can list the moderation queue and approve.
    $this->actingAsIn($this->client, $this->tenant);
    expect($this->getJson('/api/v1/foods?q=mystery')->json('data'))->toBeEmpty();
    expectApiError($this->postJson('/api/v1/foods', ['name' => 'x', 'per_100g' => ['kcal' => 1]]), 403, 'forbidden');

    $this->actingAsIn($this->owner, $this->tenant);
    expect($this->getJson('/api/v1/foods?moderation=pending')->json('data.0.id'))->toBe($pending->json('data.id'));
    $this->patchJson('/api/v1/foods/'.$pending->json('data.id'), ['moderation' => 'approved', 'is_verified' => true])->assertOk()->assertJsonPath('data.is_verified', true);
    $this->actingAsIn($this->client, $this->tenant);
    expect($this->getJson('/api/v1/foods?q=mystery')->json('data'))->toHaveCount(1);
});

it('saves quick adds as pending user-submitted foods', function () {
    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/food-logs', [
        'log_date' => '2026-11-01', 'slot' => 'snack', 'name' => 'Grandma’s maamoul', 'quantity' => 50, 'unit' => 'g',
        'kcal' => 210, 'protein_g' => 3, 'carbs_g' => 30, 'fat_g' => 9, 'source' => 'quick', 'save_food' => true,
    ])->assertCreated();

    $food = Food::withoutGlobalScopes()->where('source', 'user')->sole();
    expect($food->moderation)->toBe('pending')
        ->and($food->submitted_by)->toBe($this->client->id)
        ->and($food->tenant_id)->toBe($this->tenant->id)
        ->and($food->kcal)->toBe(420.0);
    // The submitter can still find their own pending food.
    expect($this->getJson('/api/v1/foods?q=maamoul')->json('data.0.id'))->toBe($food->id);
});
