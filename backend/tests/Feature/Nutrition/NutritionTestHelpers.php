<?php

use App\Domain\Nutrition\Models\Food;

if (! function_exists('nutritionFood')) {
    /** Creates a global food (per 100 g). */
    function nutritionFood(string $name, float $kcal, float $p, float $c, float $f, array $extra = []): Food
    {
        $food = new Food(array_merge([
            'name' => ['en' => $name, 'fr' => $name.' (fr)', 'ar' => $name, 'es' => $name],
            'kcal' => $kcal, 'protein_g' => $p, 'carbs_g' => $c, 'fat_g' => $f,
            'category' => 'other', 'moderation' => 'approved', 'source' => 'usda',
        ], $extra));
        $food->tenant_id = $extra['tenant_id'] ?? null;
        $food->save();

        return $food;
    }
}

if (! function_exists('nutritionPlanPayload')) {
    /** @param list<array<string, mixed>> $items */
    function nutritionPlanPayload(array $items, array $extra = []): array
    {
        return array_merge([
            'name' => 'Cut phase',
            'type' => 'fixed',
            'days' => [[
                'day_number' => 1,
                'label' => 'Day 1',
                'meals' => [['slot' => 'breakfast', 'time' => '08:00', 'items' => $items]],
            ]],
        ], $extra);
    }
}
