<?php

namespace App\Domain\Nutrition\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\Meal;
use App\Domain\Nutrition\Models\MealItem;
use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Nutrition\Models\MealPlanDay;
use App\Domain\Nutrition\Models\Recipe;
use App\Domain\Shared\Support\ApiException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Meal plan persistence, assignment, client day resolution, grocery lists and swaps. */
final class MealPlanService
{
    public const SLOTS = ['breakfast', 'lunch', 'dinner', 'snack', 'suhoor', 'iftar', 'custom'];

    public const TYPES = ['fixed', 'flexible', 'portion', 'fasting', 'ramadan'];

    public const AISLES = ['produce', 'meat', 'seafood', 'dairy', 'bakery', 'grains', 'pantry', 'drinks', 'frozen', 'snacks', 'prepared', 'other'];

    /** Relations needed to render a full plan. */
    public const WITH = ['days.meals.items.food', 'days.meals.items.recipe', 'client'];

    public function __construct(
        private readonly MacroCalculator $macros,
        private readonly FastingService $fasting,
    ) {}

    /**
     * Creates or fully replaces a plan (days → meals → items when `days` is present).
     *
     * @param  array<string, mixed>  $data
     */
    public function save(array $data, ?MealPlan $plan = null, ?User $author = null): MealPlan
    {
        return DB::transaction(function () use ($data, $plan, $author) {
            $attributes = array_intersect_key($data, array_flip(['name', 'type', 'diet_tags', 'is_template', 'fasting', 'start_date', 'status', 'client_id']));
            if (($attributes['type'] ?? $plan?->type) === 'ramadan' && ! empty($attributes['fasting'] ?? $plan?->fasting)) {
                $this->fasting->location($attributes['fasting'] ?? $plan->fasting); // validates city / coordinates
            }

            if ($plan === null) {
                $plan = MealPlan::create($attributes + ['created_by' => $author?->id, 'type' => 'fixed', 'status' => 'draft']);
            } else {
                $plan->update($attributes);
            }

            if (array_key_exists('days', $data)) {
                $this->replaceDays($plan, $data['days'] ?? []);
            }
            if ($plan->status === 'active' && $plan->client_id) {
                $this->archiveOtherActive($plan);
            }

            return $plan->refresh()->load(self::WITH);
        });
    }

    /** @param list<array<string, mixed>> $days */
    private function replaceDays(MealPlan $plan, array $days): void
    {
        $this->assertItemsVisible($days);
        MealPlanDay::query()->where('meal_plan_id', $plan->id)->get()->each->delete();

        foreach (array_values($days) as $dIndex => $day) {
            $dayModel = MealPlanDay::create([
                'meal_plan_id' => $plan->id,
                'day_number' => (int) ($day['day_number'] ?? $dIndex + 1),
                'label' => $day['label'] ?? null,
                'day_type' => $day['day_type'] ?? 'all',
            ]);
            foreach (array_values($day['meals'] ?? []) as $mIndex => $meal) {
                $mealModel = Meal::create([
                    'meal_plan_day_id' => $dayModel->id,
                    'slot' => $meal['slot'],
                    'name' => $meal['name'] ?? null,
                    'time' => $meal['time'] ?? null,
                    'position' => (int) ($meal['position'] ?? $mIndex),
                ]);
                foreach (array_values($meal['items'] ?? []) as $iIndex => $item) {
                    MealItem::create([
                        'meal_id' => $mealModel->id,
                        'food_id' => $item['food_id'] ?? null,
                        'recipe_id' => $item['recipe_id'] ?? null,
                        'quantity' => (float) $item['quantity'],
                        'unit' => $item['unit'] ?? (! empty($item['recipe_id']) ? 'serving' : 'g'),
                        'position' => (int) ($item['position'] ?? $iIndex),
                    ]);
                }
            }
        }
    }

    /**
     * Foods/recipes referenced by a plan must be visible to the tenant (global or own).
     *
     * @param  list<array<string, mixed>>  $days
     */
    private function assertItemsVisible(array $days): void
    {
        $foodIds = [];
        $recipeIds = [];
        foreach ($days as $day) {
            foreach ($day['meals'] ?? [] as $meal) {
                foreach ($meal['items'] ?? [] as $item) {
                    if (! empty($item['food_id'])) {
                        $foodIds[] = $item['food_id'];
                    }
                    if (! empty($item['recipe_id'])) {
                        $recipeIds[] = $item['recipe_id'];
                    }
                }
            }
        }
        $foodIds = array_values(array_unique($foodIds));
        $recipeIds = array_values(array_unique($recipeIds));
        $foods = $foodIds ? Food::query()->whereIn('id', $foodIds)->get()->keyBy('id') : collect();
        if ($foods->count() !== count($foodIds)) {
            throw ApiException::invalid('validation_failed', __('nutrition.errors.food_not_found'), ['days' => [__('nutrition.errors.food_not_found')]]);
        }
        if ($recipeIds && Recipe::query()->whereIn('id', $recipeIds)->count() !== count($recipeIds)) {
            throw ApiException::invalid('validation_failed', __('nutrition.errors.recipe_not_found'), ['days' => [__('nutrition.errors.recipe_not_found')]]);
        }
        // Units must be resolvable for each food.
        foreach ($days as $day) {
            foreach ($day['meals'] ?? [] as $meal) {
                foreach ($meal['items'] ?? [] as $item) {
                    if (! empty($item['food_id'])) {
                        $this->macros->gramsFor($foods[$item['food_id']], (float) $item['quantity'], (string) ($item['unit'] ?? 'g'));
                    }
                }
            }
        }
    }

    private function archiveOtherActive(MealPlan $plan): void
    {
        MealPlan::query()
            ->where('client_id', $plan->client_id)
            ->where('status', 'active')
            ->whereKeyNot($plan->id)
            ->update(['status' => 'archived']);
    }

    /**
     * Assigns a plan to clients: each client receives their own active copy.
     *
     * @param  list<User>  $clients
     * @return list<MealPlan>
     */
    public function assign(MealPlan $source, array $clients, ?string $startDate, ?User $author = null): array
    {
        $source->loadMissing('days.meals.items');

        return DB::transaction(function () use ($source, $clients, $startDate, $author) {
            $result = [];
            foreach ($clients as $client) {
                $copy = $source->replicate(['client_id', 'status', 'is_template', 'start_date', 'created_by']);
                $copy->fill([
                    'client_id' => $client->id,
                    'status' => 'active',
                    'is_template' => false,
                    'start_date' => $startDate ?? now($client->timezone ?: 'UTC')->toDateString(),
                    'created_by' => $author?->id ?? $source->created_by,
                ])->save();

                foreach ($source->days as $day) {
                    $newDay = $day->replicate(['meal_plan_id']);
                    $newDay->meal_plan_id = $copy->id;
                    $newDay->save();
                    foreach ($day->meals as $meal) {
                        $newMeal = $meal->replicate(['meal_plan_day_id']);
                        $newMeal->meal_plan_day_id = $newDay->id;
                        $newMeal->save();
                        foreach ($meal->items as $item) {
                            $newItem = $item->replicate(['meal_id']);
                            $newItem->meal_id = $newMeal->id;
                            $newItem->save();
                        }
                    }
                }
                $this->archiveOtherActive($copy);
                $result[] = $copy->load(self::WITH);
            }

            return $result;
        });
    }

    /** The client's active plan on a date (latest start date wins). */
    public function activePlanFor(string $clientId, string $date): ?MealPlan
    {
        return MealPlan::query()
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', $date))
            ->orderByDesc('start_date')
            ->orderByDesc('updated_at')
            ->with(['days.meals.items.food', 'days.meals.items.recipe'])
            ->first();
    }

    /** The plan day that applies on a date: plans cycle through their days from start_date (or by weekday). */
    public function dayFor(MealPlan $plan, string $date): ?MealPlanDay
    {
        $days = $plan->days->sortBy('day_number')->values();
        if ($days->isEmpty()) {
            return null;
        }
        $d = CarbonImmutable::parse($date);
        $offset = $plan->start_date
            ? (int) CarbonImmutable::parse($plan->start_date->toDateString())->diffInDays($d, false)
            : $d->dayOfWeekIso - 1;
        $index = (($offset % $days->count()) + $days->count()) % $days->count();

        return $days->firstWhere('day_number', $index + 1) ?? $days[$index];
    }

    /**
     * Planned items of a meal in contract shape (PlannedItem), flagged as logged for the date.
     *
     * @param  Collection<int, FoodLog>|null  $logs  the client's logs of that date
     * @return list<array<string, mixed>>
     */
    public function plannedItems(Meal $meal, ?Collection $logs = null): array
    {
        $loggedIds = $logs ? $logs->pluck('meal_item_id')->filter()->flip() : collect();
        $out = [];
        foreach ($meal->items as $item) {
            $macros = $this->macros->forMealItem($item);
            $out[] = [
                'meal_item_id' => $item->id,
                'food_id' => $item->food_id,
                'recipe_id' => $item->recipe_id,
                'name' => $this->itemName($item),
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit,
                ...$macros,
                'logged' => $loggedIds->has($item->id),
            ];
        }

        return $out;
    }

    public function itemName(MealItem $item): string
    {
        if ($item->recipe_id && $item->recipe) {
            return (string) $item->recipe->translate('name');
        }
        if ($item->food_id && $item->food) {
            return (string) $item->food->translate('name');
        }

        return '';
    }

    /**
     * Meals of a plan day with Ramadan shifting applied.
     *
     * @return list<array{meal: Meal, slot: string, time: string|null}>
     */
    public function mealsFor(MealPlan $plan, MealPlanDay $day, string $date): array
    {
        $times = $plan->type === 'ramadan' ? $this->fasting->ramadanTimes($date, $plan->fasting ?? []) : null;
        $out = [];
        foreach ($day->meals->sortBy('position') as $meal) {
            if ($times) {
                $shift = $this->fasting->shiftMeal($meal->slot, $times);
                $out[] = ['meal' => $meal, 'slot' => $shift['slot'], 'time' => $shift['time']];
            } else {
                $out[] = ['meal' => $meal, 'slot' => $meal->slot, 'time' => $meal->time ? substr((string) $meal->time, 0, 5) : null];
            }
        }

        return $out;
    }

    /**
     * Client-facing plan payload: {id, name, type, days: [{day_number, label, meals: [{slot, name, time, items}]}]}.
     *
     * @return array<string, mixed>
     */
    public function clientPayload(MealPlan $plan, string $date, ?Collection $logsOfDate = null): array
    {
        $today = $this->dayFor($plan, $date);
        $days = [];
        foreach ($plan->days->sortBy('day_number') as $day) {
            $meals = [];
            foreach ($this->mealsFor($plan, $day, $date) as $row) {
                $meals[] = [
                    'slot' => $row['slot'],
                    'name' => $row['meal']->name ?: __('nutrition.slots.'.$row['slot']),
                    'time' => $row['time'],
                    'items' => $this->plannedItems($row['meal'], $today && $today->id === $day->id ? $logsOfDate : null),
                ];
            }
            $days[] = ['day_number' => (int) $day->day_number, 'label' => $day->label, 'meals' => $meals];
        }

        return ['id' => $plan->id, 'name' => $plan->name, 'type' => $plan->type, 'days' => $days];
    }

    /**
     * Totals of one day of a plan.
     *
     * @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float}
     */
    public function dayTotals(MealPlanDay $day): array
    {
        $rows = [];
        foreach ($day->meals as $meal) {
            foreach ($meal->items as $item) {
                $rows[] = $this->macros->forMealItem($item);
            }
        }

        return MacroCalculator::sum($rows);
    }

    /**
     * Grocery list: total grams per food across all plan days (recipes expanded), grouped by aisle.
     *
     * @return list<array{aisle: string, label: string, items: list<array<string, mixed>>}>
     */
    public function groceryList(MealPlan $plan, int $repeat = 1): array
    {
        $plan->loadMissing(['days.meals.items.food', 'days.meals.items.recipe.items.food']);
        $grams = [];
        $foods = [];
        $add = function (?Food $food, float $g) use (&$grams, &$foods): void {
            if (! $food || $g <= 0) {
                return;
            }
            $foods[$food->id] = $food;
            $grams[$food->id] = ($grams[$food->id] ?? 0) + $g;
        };

        foreach ($plan->days as $day) {
            foreach ($day->meals as $meal) {
                foreach ($meal->items as $item) {
                    if ($item->recipe_id && $item->recipe) {
                        $recipe = $item->recipe;
                        $totalGrams = (float) $recipe->items->sum('grams');
                        $factor = in_array($item->unit, ['serving', 'servings', 'portion', null, ''], true)
                            ? (float) $item->quantity / max(1, (int) $recipe->servings)
                            : ($totalGrams > 0 ? $this->macroMass((float) $item->quantity, (string) $item->unit) / $totalGrams : 0);
                        foreach ($recipe->items as $ri) {
                            $add($ri->food, (float) $ri->grams * $factor);
                        }
                    } elseif ($item->food) {
                        $add($item->food, $this->macros->gramsFor($item->food, (float) $item->quantity, (string) $item->unit));
                    }
                }
            }
        }

        $groups = [];
        foreach ($grams as $foodId => $g) {
            $food = $foods[$foodId];
            $aisle = in_array($food->category, self::AISLES, true) ? $food->category : 'other';
            $total = $g * max(1, $repeat);
            $serving = $food->servingList()[0] ?? null;
            $groups[$aisle][] = [
                'food_id' => $food->id,
                'name' => $food->translate('name'),
                'grams' => round($total),
                'servings' => $serving ? ['unit' => $serving['unit'], 'count' => round($total / $serving['grams'], 1)] : null,
            ];
        }

        $out = [];
        foreach (self::AISLES as $aisle) {
            if (! empty($groups[$aisle])) {
                usort($groups[$aisle], fn ($a, $b) => strcmp((string) $a['name'], (string) $b['name']));
                $out[] = ['aisle' => $aisle, 'label' => __('nutrition.aisles.'.$aisle), 'items' => $groups[$aisle]];
            }
        }

        return $out;
    }

    private function macroMass(float $quantity, string $unit): float
    {
        return $quantity * (MacroCalculator::MASS_UNITS[strtolower($unit)] ?? 1.0);
    }

    /**
     * Swap candidates for a plan item: same category foods scaled to the same kcal whose
     * protein/carbs/fat energy each stays within ±10% of the item's kcal; recipes within ±10% kcal.
     *
     * @return list<array<string, mixed>>
     */
    public function swaps(MealPlan $plan, MealItem $item, int $limit = 10): array
    {
        $item->loadMissing(['food', 'recipe']);
        $target = $this->macros->forMealItem($item);
        $kcal = max(1.0, $target['kcal']);
        $tags = $plan->diet_tags ?? [];
        $out = [];

        if ($item->recipe_id) {
            $recipes = Recipe::query()->whereKeyNot($item->recipe_id)->where('is_published', true)
                ->where('kcal', '>', 0)->get();
            foreach ($recipes as $recipe) {
                $servings = $kcal / max(0.1, (float) $recipe->kcal);
                if ($servings < 0.5 || $servings > 2) {
                    continue;
                }
                $quantity = round($servings * 4) / 4; // quarter servings
                $m = $this->macros->forRecipe($recipe, $quantity, 'serving');
                $diff = ($m['kcal'] - $kcal) / $kcal;
                if (abs($diff) <= 0.10) {
                    $out[] = ['food' => null, 'recipe' => ['id' => $recipe->id, 'name' => $recipe->translate('name')], 'quantity' => $quantity, 'unit' => 'serving', ...$m, 'kcal_diff_pct' => round($diff * 100, 1), '_score' => abs($diff)];
                }
            }
        } elseif ($item->food) {
            $source = $item->food;
            $candidates = Food::query()
                ->whereKeyNot($source->id)
                ->where('moderation', 'approved')
                ->where('kcal', '>', 0)
                ->when($source->category, fn ($q) => $q->where('category', $source->category))
                ->limit(500)
                ->get();
            foreach ($candidates as $food) {
                if ($tags && array_diff($tags, $food->diet_tags ?? [])) {
                    continue;
                }
                $grams = round($kcal / (float) $food->kcal * 100 / 5) * 5;
                if ($grams <= 0) {
                    continue;
                }
                $m = $this->macros->forGrams($food, $grams);
                $dp = abs($m['protein_g'] - $target['protein_g']) * 4 / $kcal;
                $dc = abs($m['carbs_g'] - $target['carbs_g']) * 4 / $kcal;
                $df = abs($m['fat_g'] - $target['fat_g']) * 9 / $kcal;
                $dk = ($m['kcal'] - $kcal) / $kcal;
                if (abs($dk) <= 0.10 && $dp <= 0.10 && $dc <= 0.10 && $df <= 0.10) {
                    $out[] = ['food' => $food, 'recipe' => null, 'quantity' => $grams, 'unit' => 'g', ...$m, 'kcal_diff_pct' => round($dk * 100, 1), '_score' => $dp + $dc + $df + abs($dk)];
                }
            }
        }

        usort($out, fn ($a, $b) => $a['_score'] <=> $b['_score']);

        return array_map(function ($row) {
            unset($row['_score']);

            return $row;
        }, array_slice($out, 0, $limit));
    }

    /** Applies a swap to an item. */
    public function applySwap(MealItem $item, ?string $foodId, ?string $recipeId, float $quantity, string $unit): MealItem
    {
        if ($foodId) {
            $food = Food::query()->findOrFail($foodId);
            $this->macros->gramsFor($food, $quantity, $unit);
        } else {
            Recipe::query()->findOrFail($recipeId);
        }
        $item->update(['food_id' => $foodId, 'recipe_id' => $foodId ? null : $recipeId, 'quantity' => $quantity, 'unit' => $unit]);

        return $item->refresh()->load(['food', 'recipe']);
    }
}
