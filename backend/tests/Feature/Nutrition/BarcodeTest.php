<?php

use App\Domain\Nutrition\Models\Food;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner);
});

function offProduct(): array
{
    return [
        'status' => 1,
        'code' => '6291003000012',
        'product' => [
            'product_name' => 'Laban Up',
            'product_name_ar' => 'لبن أب',
            'brands' => 'Almarai, Other',
            'nutriments' => ['energy-kcal_100g' => 44, 'proteins_100g' => 3.1, 'carbohydrates_100g' => 4.6, 'fat_100g' => 1.5, 'sugars_100g' => 4.6, 'salt_100g' => 0.25],
            'serving_quantity' => 180,
            'serving_size' => '180 ml',
            'labels_tags' => ['en:halal'],
            'allergens_tags' => ['en:milk'],
            'categories_tags' => ['en:dairies', 'en:fermented-milk-products'],
        ],
    ];
}

it('falls back to Open Food Facts, saves the product and caches it', function () {
    Http::fake(['*/api/v2/product/6291003000012.json*' => Http::response(offProduct())]);

    $response = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/foods/barcode/6291003000012')->assertOk();
    $response->assertJsonPath('data.source', 'off')
        ->assertJsonPath('data.brand', 'Almarai')
        ->assertJsonPath('data.barcode', '6291003000012')
        ->assertJsonPath('data.per_100g.kcal', 44)
        ->assertJsonPath('data.category', 'dairy')
        ->assertJsonPath('data.servings.0.grams', 180)
        ->assertJsonPath('data.allergens.0', 'milk');
    expect($response->json('data.diet_tags'))->toContain('halal');

    $food = Food::withoutGlobalScopes()->where('barcode', '6291003000012')->sole();
    expect($food->tenant_id)->toBeNull()->and($food->name['ar'])->toBe('لبن أب');

    // Second scan is served from the database.
    $this->getJson('/api/v1/foods/barcode/6291003000012')->assertOk()->assertJsonPath('data.id', $food->id);
    Http::assertSentCount(1);
});

it('returns not_found for unknown barcodes and caches the miss', function () {
    Http::fake(['*' => Http::response(['status' => 0, 'status_verbose' => 'product not found'])]);
    $this->actingAsIn($this->client, $this->tenant);

    expectApiError($this->getJson('/api/v1/foods/barcode/0000000000000'), 404, 'not_found');
    expectApiError($this->getJson('/api/v1/foods/barcode/0000000000000'), 404, 'not_found');
    Http::assertSentCount(1);
});

it('prefers a local tenant food with the barcode without calling the API', function () {
    Http::fake();
    $this->inTenant($this->tenant, fn () => Food::create(['name' => ['en' => 'House protein bar'], 'barcode' => '1234567890123', 'kcal' => 380, 'protein_g' => 30]));

    $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/foods/barcode/1234567890123')
        ->assertOk()->assertJsonPath('data.name', 'House protein bar')->assertJsonPath('data.is_custom', true);
    Http::assertNothingSent();
});
