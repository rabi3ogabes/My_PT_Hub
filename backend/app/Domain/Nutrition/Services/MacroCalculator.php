<?php

namespace App\Domain\Nutrition\Services;

use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\MealItem;
use App\Domain\Nutrition\Models\Recipe;
use App\Domain\Shared\Support\ApiException;

/**
 * Turns foods/recipes + a quantity and unit into macros. All values are
 * computed server-side from per-100 g data so clients never send macros for
 * known foods.
 */
final class MacroCalculator
{
    /** Fixed mass units (grams per unit). */
    public const MASS_UNITS = ['g' => 1.0, 'kg' => 1000.0, 'ml' => 1.0, 'l' => 1000.0, 'oz' => 28.349523125, 'lb' => 453.59237];

    /** @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float} */
    public static function zero(): array
    {
        return ['kcal' => 0.0, 'protein_g' => 0.0, 'carbs_g' => 0.0, 'fat_g' => 0.0];
    }

    /**
     * Grams represented by a quantity of a food in a unit (g, kg, oz, "serving" or a named serving unit).
     */
    public function gramsFor(Food $food, float $quantity, string $unit): float
    {
        $unit = strtolower(trim($unit));
        if (isset(self::MASS_UNITS[$unit])) {
            return $quantity * self::MASS_UNITS[$unit];
        }
        $servings = $food->servingList();
        if ($unit === 'serving') {
            return $quantity * ($servings[0]['grams'] ?? 100.0);
        }
        foreach ($servings as $serving) {
            if (strtolower($serving['unit']) === $unit) {
                return $quantity * $serving['grams'];
            }
        }

        throw ApiException::invalid('invalid_unit', __('nutrition.errors.invalid_unit', ['unit' => $unit]), ['unit' => [__('nutrition.errors.invalid_unit', ['unit' => $unit])]]);
    }

    /** @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float} */
    public function forGrams(Food $food, float $grams): array
    {
        $f = $grams / 100;

        return self::round([
            'kcal' => (float) $food->kcal * $f,
            'protein_g' => (float) $food->protein_g * $f,
            'carbs_g' => (float) $food->carbs_g * $f,
            'fat_g' => (float) $food->fat_g * $f,
        ]);
    }

    /** @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float} */
    public function forFood(Food $food, float $quantity, string $unit = 'g'): array
    {
        return $this->forGrams($food, $this->gramsFor($food, $quantity, $unit));
    }

    /**
     * Raw totals of a recipe (whole batch) from its items. Items must have `food` loaded.
     *
     * @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float, grams: float}
     */
    public function recipeTotals(Recipe $recipe): array
    {
        $totals = self::zero() + ['grams' => 0.0];
        foreach ($recipe->items as $item) {
            $food = $item->food;
            if (! $food) {
                continue;
            }
            $f = (float) $item->grams / 100;
            $totals['kcal'] += (float) $food->kcal * $f;
            $totals['protein_g'] += (float) $food->protein_g * $f;
            $totals['carbs_g'] += (float) $food->carbs_g * $f;
            $totals['fat_g'] += (float) $food->fat_g * $f;
            $totals['grams'] += (float) $item->grams;
        }

        return $totals;
    }

    /** @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float} */
    public function recipePerServing(Recipe $recipe): array
    {
        $totals = $this->recipeTotals($recipe);
        $servings = max(1, (int) $recipe->servings);

        return self::round([
            'kcal' => $totals['kcal'] / $servings,
            'protein_g' => $totals['protein_g'] / $servings,
            'carbs_g' => $totals['carbs_g'] / $servings,
            'fat_g' => $totals['fat_g'] / $servings,
        ]);
    }

    /** Recomputes and stores the cached per-serving macros of a recipe. */
    public function refreshRecipe(Recipe $recipe): Recipe
    {
        $recipe->load('items.food');
        $recipe->forceFill($this->recipePerServing($recipe))->save();

        return $recipe;
    }

    /**
     * Macros for a quantity of a recipe: unit "serving" (default) or grams of the cooked batch.
     *
     * @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float}
     */
    public function forRecipe(Recipe $recipe, float $quantity, string $unit = 'serving'): array
    {
        $unit = strtolower(trim($unit));
        $perServing = ['kcal' => (float) $recipe->kcal, 'protein_g' => (float) $recipe->protein_g, 'carbs_g' => (float) $recipe->carbs_g, 'fat_g' => (float) $recipe->fat_g];
        if (in_array($unit, ['serving', 'servings', 'portion'], true)) {
            return self::scale($perServing, $quantity);
        }
        if (isset(self::MASS_UNITS[$unit])) {
            $recipe->loadMissing('items.food');
            $totals = $this->recipeTotals($recipe);
            if ($totals['grams'] <= 0) {
                return self::zero();
            }
            $f = ($quantity * self::MASS_UNITS[$unit]) / $totals['grams'];

            return self::round(['kcal' => $totals['kcal'] * $f, 'protein_g' => $totals['protein_g'] * $f, 'carbs_g' => $totals['carbs_g'] * $f, 'fat_g' => $totals['fat_g'] * $f]);
        }

        throw ApiException::invalid('invalid_unit', __('nutrition.errors.invalid_unit', ['unit' => $unit]), ['unit' => [__('nutrition.errors.invalid_unit', ['unit' => $unit])]]);
    }

    /**
     * Macros of a meal plan item (needs food/recipe loaded).
     *
     * @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float}
     */
    public function forMealItem(MealItem $item): array
    {
        if ($item->recipe_id && $item->recipe) {
            return $this->forRecipe($item->recipe, (float) $item->quantity, $item->unit ?: 'serving');
        }
        if ($item->food_id && $item->food) {
            return $this->forFood($item->food, (float) $item->quantity, $item->unit ?: 'g');
        }

        return self::zero();
    }

    /**
     * @param  array<string, float>  $macros
     * @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float}
     */
    public static function scale(array $macros, float $factor): array
    {
        return self::round([
            'kcal' => ($macros['kcal'] ?? 0) * $factor,
            'protein_g' => ($macros['protein_g'] ?? 0) * $factor,
            'carbs_g' => ($macros['carbs_g'] ?? 0) * $factor,
            'fat_g' => ($macros['fat_g'] ?? 0) * $factor,
        ]);
    }

    /**
     * @param  iterable<array<string, float|int|null>>  $rows
     * @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float}
     */
    public static function sum(iterable $rows): array
    {
        $total = self::zero();
        foreach ($rows as $row) {
            foreach ($total as $key => $value) {
                $total[$key] = $value + (float) ($row[$key] ?? 0);
            }
        }

        return self::round($total);
    }

    /**
     * @param  array<string, float|int>  $macros
     * @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float}
     */
    public static function round(array $macros): array
    {
        return [
            'kcal' => round((float) ($macros['kcal'] ?? 0), 1),
            'protein_g' => round((float) ($macros['protein_g'] ?? 0), 1),
            'carbs_g' => round((float) ($macros['carbs_g'] ?? 0), 1),
            'fat_g' => round((float) ($macros['fat_g'] ?? 0), 1),
        ];
    }
}
