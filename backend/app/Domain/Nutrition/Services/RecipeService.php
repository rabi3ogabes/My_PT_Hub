<?php

namespace App\Domain\Nutrition\Services;

use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\Recipe;
use App\Domain\Nutrition\Models\RecipeItem;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\V1\Support\TranslatableInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Recipe persistence: items replaced as a whole, per-serving macros recomputed on every save. */
final class RecipeService
{
    public function __construct(private readonly MacroCalculator $macros) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Recipe $recipe = null): Recipe
    {
        return DB::transaction(function () use ($data, $recipe) {
            $attributes = [];
            foreach (['name', 'description'] as $key) {
                if (array_key_exists($key, $data)) {
                    $attributes[$key] = TranslatableInput::value($data[$key], $recipe?->{$key});
                }
            }
            if (array_key_exists('steps', $data)) {
                $attributes['steps'] = $this->steps($data['steps'], $recipe?->steps);
            }
            foreach (['cuisine', 'prep_minutes', 'cook_minutes', 'servings', 'tags', 'photo_url', 'is_published'] as $key) {
                if (array_key_exists($key, $data)) {
                    $attributes[$key] = $data[$key];
                }
            }

            if ($recipe === null) {
                $name = $attributes['name'] ?? [];
                $attributes['slug'] = $this->slug((string) ($name['en'] ?? $name['fr'] ?? (array_values($name)[0] ?? 'recipe')));
                $recipe = Recipe::create($attributes);
            } else {
                $recipe->update($attributes);
            }

            if (array_key_exists('items', $data)) {
                $this->replaceItems($recipe, $data['items'] ?? []);
            }

            return $this->macros->refreshRecipe($recipe);
        });
    }

    /** @param list<array<string, mixed>> $items */
    private function replaceItems(Recipe $recipe, array $items): void
    {
        $ids = array_values(array_unique(array_column($items, 'food_id')));
        $visible = Food::query()->whereIn('id', $ids)->pluck('id')->all();
        if (count($visible) !== count($ids)) {
            throw ApiException::invalid('validation_failed', __('nutrition.errors.food_not_found'), ['items' => [__('nutrition.errors.food_not_found')]]);
        }
        RecipeItem::query()->where('recipe_id', $recipe->id)->delete();
        foreach (array_values($items) as $i => $item) {
            RecipeItem::create([
                'recipe_id' => $recipe->id,
                'food_id' => $item['food_id'],
                'grams' => (float) $item['grams'],
                'display_qty' => $item['display_qty'] ?? null,
                'display_unit' => $item['display_unit'] ?? null,
                'position' => $i,
            ]);
        }
    }

    /**
     * Steps: a list of strings (current locale) or {locale: [strings]}.
     *
     * @param  array<string, mixed>|null  $existing
     * @return array<string, list<string>>
     */
    private function steps(mixed $input, ?array $existing): array
    {
        if (is_array($input) && array_is_list($input)) {
            return array_merge($existing ?? [], [app()->getLocale() => array_values(array_map('strval', $input))]);
        }
        $out = [];
        foreach ((array) $input as $locale => $list) {
            if (in_array($locale, TranslatableInput::LOCALES, true) && is_array($list)) {
                $out[$locale] = array_values(array_map('strval', $list));
            }
        }

        return $out;
    }

    private function slug(string $name): string
    {
        $base = Str::slug($name) ?: 'recipe';
        $slug = $base;
        $i = 1;
        while (Recipe::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
