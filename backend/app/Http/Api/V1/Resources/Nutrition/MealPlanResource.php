<?php

namespace App\Http\Api\V1\Resources\Nutrition;

use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Nutrition\Services\MacroCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MealPlan Staff view of a meal plan with live macro totals. */
class MealPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $calc = app(MacroCalculator::class);
        $dayTotals = [];
        $days = $this->relationLoaded('days') ? $this->days->sortBy('day_number')->values()->map(function ($day) use ($calc, $request, &$dayTotals) {
            $mealRows = [];
            $meals = $day->meals->sortBy('position')->values()->map(function ($meal) use ($calc, $request, &$mealRows) {
                $itemRows = [];
                $items = $meal->items->map(function ($item) use ($calc, $request, &$itemRows) {
                    $m = $calc->forMealItem($item);
                    $itemRows[] = $m;

                    return [
                        'id' => $item->id,
                        'food_id' => $item->food_id,
                        'recipe_id' => $item->recipe_id,
                        'name' => $item->recipe ? $item->recipe->translate('name') : ($item->food ? $item->food->translate('name') : ''),
                        'quantity' => (float) $item->quantity,
                        'unit' => $item->unit,
                        ...$m,
                        'food' => $item->food && ! $item->recipe_id ? (new FoodResource($item->food))->resolve($request) : null,
                        'recipe' => $item->recipe ? ['id' => $item->recipe->id, 'name' => $item->recipe->translate('name'), 'servings' => (int) $item->recipe->servings] : null,
                    ];
                })->values()->all();
                $totals = MacroCalculator::sum($itemRows);
                $mealRows[] = $totals;

                return [
                    'id' => $meal->id,
                    'slot' => $meal->slot,
                    'name' => $meal->name ?: __('nutrition.slots.'.$meal->slot),
                    'time' => $meal->time ? substr((string) $meal->time, 0, 5) : null,
                    'position' => (int) $meal->position,
                    'totals' => $totals,
                    'items' => $items,
                ];
            })->all();
            $totals = MacroCalculator::sum($mealRows);
            $dayTotals[] = $totals;

            return [
                'id' => $day->id,
                'day_number' => (int) $day->day_number,
                'label' => $day->label,
                'day_type' => $day->day_type,
                'totals' => $totals,
                'meals' => $meals,
            ];
        })->all() : [];

        $avg = $dayTotals ? MacroCalculator::scale(MacroCalculator::sum($dayTotals), 1 / count($dayTotals)) : MacroCalculator::zero();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'diet_tags' => array_values($this->diet_tags ?? []),
            'is_template' => (bool) $this->is_template,
            'fasting' => $this->fasting ? (object) $this->fasting : null,
            'start_date' => $this->start_date?->toDateString(),
            'client' => $this->client_id && $this->relationLoaded('client') && $this->client ? ['id' => $this->client->id, 'name' => $this->client->name] : null,
            'daily_average' => $avg,
            'days_count' => $this->relationLoaded('days') ? $this->days->count() : null,
            'days' => $days,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
