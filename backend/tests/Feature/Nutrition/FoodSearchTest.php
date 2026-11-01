<?php

use App\Domain\Nutrition\Models\Food;
use Database\Seeders\Modules\FoodSeeder;

beforeEach(function () {
    $this->seed(FoodSeeder::class);
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner);
});

it('seeds at least 250 global foods with names in four languages', function () {
    $foods = Food::withoutGlobalScopes()->whereNull('tenant_id')->get();
    expect($foods->count())->toBeGreaterThanOrEqual(250);
    $foods->each(fn (Food $f) => expect(array_keys($f->name))->toContain('en', 'ar', 'fr', 'es'));
});

it('finds kabsa when searching in Arabic', function () {
    $response = $this->actingAsIn($this->client, $this->tenant)
        ->getJson('/api/v1/foods?lang=ar&q='.urlencode('كبسة'))
        ->assertOk();

    $names = collect($response->json('data'))->pluck('name');
    expect($names)->toContain('كبسة دجاج');
    $kabsa = collect($response->json('data'))->firstWhere('name', 'كبسة دجاج');
    expect($kabsa['per_100g']['kcal'])->toEqual(170.0)
        ->and($kabsa['per_100g']['protein_g'])->toEqual(9.8)
        ->and($kabsa['servings'][0]['unit'])->toBe('plate')
        ->and($kabsa['diet_tags'])->toContain('halal');
});

it('matches Arabic spelling variants (taa marbuta / haa)', function () {
    $response = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/foods?q='.urlencode('كبسه'))->assertOk();
    expect(collect($response->json('data'))->pluck('id'))->not->toBeEmpty();
});

it('finds couscous royal when searching in French with correct macros', function () {
    $response = $this->actingAsIn($this->client, $this->tenant)
        ->getJson('/api/v1/foods?lang=fr&q='.urlencode('couscous royal'))
        ->assertOk();

    $item = collect($response->json('data'))->firstWhere('name', 'Couscous royal');
    expect($item)->not->toBeNull()
        ->and($item['per_100g']['kcal'])->toEqual(165.0)
        ->and($item['per_100g']['carbs_g'])->toEqual(17.0)
        ->and($item['region'])->toBe('maghreb');
});

it('is accent-insensitive and searches aliases', function () {
    $this->actingAsIn($this->client, $this->tenant);
    $crepe = $this->getJson('/api/v1/foods?q=crepe')->assertOk()->json('data');
    expect(collect($crepe)->pluck('name'))->toContain('Crêpe (plain)');

    $alias = $this->getJson('/api/v1/foods?q=chocolatine')->assertOk()->json('data');
    expect(collect($alias)->pluck('name'))->toContain('Pain au chocolat');
});

it('filters by region and paginates with a cursor', function () {
    $this->actingAsIn($this->client, $this->tenant);
    $page = $this->getJson('/api/v1/foods?region=gulf&per_page=10')->assertOk();
    expect($page->json('data'))->toHaveCount(10)
        ->and(collect($page->json('data'))->pluck('region')->unique()->all())->toBe(['gulf'])
        ->and($page->json('meta.next_cursor'))->not->toBeNull();

    $next = $this->getJson('/api/v1/foods?region=gulf&per_page=10&cursor='.$page->json('meta.next_cursor'))->assertOk();
    expect(collect($next->json('data'))->pluck('id')->intersect(collect($page->json('data'))->pluck('id')))->toBeEmpty();
});
