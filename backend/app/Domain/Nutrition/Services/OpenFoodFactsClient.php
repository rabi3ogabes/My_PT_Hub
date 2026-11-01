<?php

namespace App\Domain\Nutrition\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Open Food Facts API v2 barcode lookup. Results (including "not found") are
 * cached so repeated scans of unknown products don't hammer the API.
 */
final class OpenFoodFactsClient
{
    private const FIELDS = 'code,product_name,product_name_en,product_name_fr,product_name_ar,product_name_es,brands,nutriments,serving_quantity,serving_size,allergens_tags,labels_tags,categories_tags';

    /**
     * @return array<string, mixed>|null normalised food attributes, or null when unknown
     */
    public function lookup(string $barcode): ?array
    {
        $cached = Cache::get($this->key($barcode));
        if (is_array($cached)) {
            return $cached['found'] ? $cached['food'] : null;
        }

        try {
            $response = Http::timeout(6)
                ->acceptJson()
                ->withUserAgent(config('brand.name', 'Aurum').' - nutrition - '.config('app.url'))
                ->get(rtrim((string) config('services.open_food_facts.base_url'), '/').'/api/v2/product/'.rawurlencode($barcode).'.json', ['fields' => self::FIELDS]);
        } catch (Throwable) {
            return null; // network failure: don't cache, try again next scan
        }

        if ($response->serverError()) {
            return null;
        }

        $json = $response->json();
        $product = is_array($json) && (int) ($json['status'] ?? 0) === 1 ? ($json['product'] ?? null) : null;
        $food = is_array($product) ? $this->normalize($barcode, $product) : null;

        Cache::put($this->key($barcode), ['found' => $food !== null, 'food' => $food], $food ? now()->addDays(30) : now()->addDay());

        return $food;
    }

    private function key(string $barcode): string
    {
        return 'off:product:'.$barcode;
    }

    /**
     * @param  array<string, mixed>  $p
     * @return array<string, mixed>|null
     */
    private function normalize(string $barcode, array $p): ?array
    {
        $n = $p['nutriments'] ?? [];
        $kcal = $n['energy-kcal_100g'] ?? (isset($n['energy_100g']) ? (float) $n['energy_100g'] / 4.184 : null);
        if ($kcal === null) {
            return null;
        }

        $names = array_filter([
            'en' => $p['product_name_en'] ?? null,
            'fr' => $p['product_name_fr'] ?? null,
            'ar' => $p['product_name_ar'] ?? null,
            'es' => $p['product_name_es'] ?? null,
        ], fn ($v) => is_string($v) && trim($v) !== '');
        $generic = is_string($p['product_name'] ?? null) ? trim($p['product_name']) : '';
        if ($generic !== '' && ! isset($names['en'])) {
            $names['en'] = $generic;
        }
        if ($names === []) {
            return null;
        }

        $servings = [];
        if (! empty($p['serving_quantity']) && (float) $p['serving_quantity'] > 0) {
            $label = is_string($p['serving_size'] ?? null) ? $p['serving_size'] : null;
            $servings[] = ['unit' => 'serving', 'grams' => round((float) $p['serving_quantity'], 1), 'label' => $label ? ['en' => $label] : null];
        }

        $labels = $p['labels_tags'] ?? [];
        $tags = [];
        foreach (['en:halal' => 'halal', 'en:vegan' => 'vegan', 'en:vegetarian' => 'vegetarian', 'en:no-gluten' => 'gluten_free', 'en:organic' => 'organic'] as $tag => $ours) {
            if (in_array($tag, $labels, true)) {
                $tags[] = $ours;
            }
        }
        $allergens = array_values(array_map(fn ($a) => str_replace('en:', '', (string) $a), array_filter($p['allergens_tags'] ?? [], fn ($a) => str_starts_with((string) $a, 'en:'))));

        $brand = is_string($p['brands'] ?? null) ? trim(explode(',', $p['brands'])[0]) : null;

        return [
            'name' => $names,
            'brand' => $brand ?: null,
            'barcode' => $barcode,
            'source' => 'off',
            'category' => $this->category($p['categories_tags'] ?? []),
            'kcal' => round((float) $kcal, 2),
            'protein_g' => round((float) ($n['proteins_100g'] ?? 0), 2),
            'carbs_g' => round((float) ($n['carbohydrates_100g'] ?? 0), 2),
            'fat_g' => round((float) ($n['fat_100g'] ?? 0), 2),
            'fiber_g' => round((float) ($n['fiber_100g'] ?? 0), 2),
            'sugar_g' => round((float) ($n['sugars_100g'] ?? 0), 2),
            'sodium_mg' => round((float) ($n['sodium_100g'] ?? (isset($n['salt_100g']) ? (float) $n['salt_100g'] / 2.5 : 0)) * 1000, 2),
            'servings' => $servings ?: null,
            'diet_tags' => $tags,
            'allergens' => $allergens,
        ];
    }

    /** @param list<string> $tags */
    private function category(array $tags): string
    {
        $joined = implode(' ', $tags);

        return match (true) {
            str_contains($joined, 'beverages') => 'drinks',
            str_contains($joined, 'dairies') || str_contains($joined, 'cheeses') || str_contains($joined, 'yogurts') => 'dairy',
            str_contains($joined, 'breads') || str_contains($joined, 'biscuits') => 'bakery',
            str_contains($joined, 'snacks') || str_contains($joined, 'chocolates') || str_contains($joined, 'confectioneries') => 'snacks',
            str_contains($joined, 'frozen') => 'frozen',
            str_contains($joined, 'meats') => 'meat',
            str_contains($joined, 'seafood') || str_contains($joined, 'fishes') => 'seafood',
            str_contains($joined, 'cereals') || str_contains($joined, 'pastas') || str_contains($joined, 'rices') => 'grains',
            str_contains($joined, 'fruits') || str_contains($joined, 'vegetables') => 'produce',
            default => 'pantry',
        };
    }
}
