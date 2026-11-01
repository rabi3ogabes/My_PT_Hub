<?php

namespace App\Domain\Nutrition\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Nutrition\Models\Recipe;
use App\Domain\Nutrition\Models\WaterLog;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The client's nutrition day: targets vs consumed, meals with logs and planned
 * items. Planned items are read live from the active plan on every request, so
 * any food a coach adds to the plan always appears (regression guarantee).
 */
final class ClientNutritionService
{
    public const DEFAULT_SLOTS = ['breakfast', 'lunch', 'dinner', 'snack'];

    public const RAMADAN_SLOTS = ['suhoor', 'iftar', 'dinner', 'snack'];

    public const DEFAULT_WATER_TARGET = 2500;

    public function __construct(
        private readonly MacroCalculator $macros,
        private readonly MealPlanService $plans,
        private readonly TargetService $targets,
        private readonly FastingService $fasting,
    ) {}

    public function today(User $client): string
    {
        return CarbonImmutable::now($client->timezone ?: 'UTC')->toDateString();
    }

    /** @return Collection<int, FoodLog> */
    public function logsFor(string $clientId, string $date): Collection
    {
        return FoodLog::query()->where('client_id', $clientId)->whereDate('log_date', $date)
            ->with(['food', 'recipe'])->orderBy('logged_at')->get();
    }

    /** @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float}|null */
    public static function targetMacros(?NutritionTarget $target): ?array
    {
        return $target ? [
            'kcal' => (float) $target->kcal,
            'protein_g' => (float) $target->protein_g,
            'carbs_g' => (float) $target->carbs_g,
            'fat_g' => (float) $target->fat_g,
        ] : null;
    }

    /**
     * GET /me/nutrition payload. Log arrays contain FoodLog models (formatted by the controller).
     *
     * @return array<string, mixed>
     */
    public function day(User $client, string $date): array
    {
        $target = $this->targets->forDate($client->id, $date);
        $logs = $this->logsFor($client->id, $date);
        $plan = $this->plans->activePlanFor($client->id, $date);
        $water = (int) WaterLog::query()->where('client_id', $client->id)->whereDate('log_date', $date)->sum('ml');

        $meals = [];
        foreach ($plan && $plan->type === 'ramadan' ? self::RAMADAN_SLOTS : self::DEFAULT_SLOTS as $slot) {
            $meals[$slot] = ['slot' => $slot, 'label' => __('nutrition.slots.'.$slot), 'planned' => []];
        }

        if ($plan && ($day = $this->plans->dayFor($plan, $date))) {
            foreach ($this->plans->mealsFor($plan, $day, $date) as $row) {
                $slot = $row['slot'];
                if (! isset($meals[$slot])) {
                    $meals[$slot] = ['slot' => $slot, 'label' => $row['meal']->name ?: __('nutrition.slots.'.$slot), 'planned' => []];
                } elseif ($row['meal']->name && $meals[$slot]['planned'] === []) {
                    $meals[$slot]['label'] = $row['meal']->name;
                }
                array_push($meals[$slot]['planned'], ...$this->plans->plannedItems($row['meal'], $logs));
            }
        }

        foreach ($logs as $log) {
            if (! isset($meals[$log->slot])) {
                $meals[$log->slot] = ['slot' => $log->slot, 'label' => __('nutrition.slots.'.$log->slot), 'planned' => []];
            }
        }

        $out = [];
        foreach ($meals as $slot => $meal) {
            $slotLogs = $logs->where('slot', $slot)->values();
            $out[] = [
                'slot' => $slot,
                'label' => $meal['label'],
                'consumed' => MacroCalculator::sum($slotLogs->map(fn ($l) => $l->only(['kcal', 'protein_g', 'carbs_g', 'fat_g']))),
                'logs' => $slotLogs->all(),
                'planned' => $meal['planned'],
            ];
        }

        return [
            'date' => $date,
            'targets' => self::targetMacros($target),
            'water_ml' => $water,
            'water_target_ml' => (int) ($target?->water_ml ?: self::DEFAULT_WATER_TARGET),
            'consumed' => MacroCalculator::sum($logs->map(fn ($l) => $l->only(['kcal', 'protein_g', 'carbs_g', 'fat_g']))),
            'meals' => $out,
            'fasting' => $this->fasting->windowFor($plan, $date),
        ];
    }

    /**
     * Last 7 days (ending on $date): per-day totals, averages over logged days and adherence
     * (share of days with a target whose kcal is within ±10%).
     *
     * @return array<string, mixed>
     */
    public function week(User $client, string $date, int $days = 7): array
    {
        $to = CarbonImmutable::parse($date);
        $from = $to->subDays($days - 1);
        $totals = FoodLog::query()->where('client_id', $client->id)
            ->whereDate('log_date', '>=', $from->toDateString())->whereDate('log_date', '<=', $to->toDateString())
            ->get(['log_date', 'kcal', 'protein_g', 'carbs_g', 'fat_g'])
            ->groupBy(fn ($l) => $l->log_date->toDateString());

        $rows = [];
        $logged = [];
        $withTarget = 0;
        $onTarget = 0;
        for ($d = $from; $d->lte($to); $d = $d->addDay()) {
            $key = $d->toDateString();
            $consumed = MacroCalculator::sum(($totals[$key] ?? collect())->map(fn ($l) => $l->only(['kcal', 'protein_g', 'carbs_g', 'fat_g'])));
            $target = self::targetMacros($this->targets->forDate($client->id, $key));
            $within = $target && $target['kcal'] > 0 && isset($totals[$key])
                ? abs($consumed['kcal'] - $target['kcal']) <= $target['kcal'] * 0.10
                : false;
            if ($target) {
                $withTarget++;
                $onTarget += $within ? 1 : 0;
            }
            if (isset($totals[$key])) {
                $logged[] = $consumed;
            }
            $rows[] = ['date' => $key, 'consumed' => $consumed, 'targets' => $target, 'logged' => isset($totals[$key]), 'within_target' => $within];
        }

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'days' => $rows,
            'averages' => $logged ? MacroCalculator::scale(MacroCalculator::sum($logged), 1 / count($logged)) : MacroCalculator::zero(),
            'logged_days' => count($logged),
            'adherence_pct' => $withTarget ? (int) round($onTarget / $withTarget * 100) : null,
        ];
    }

    /**
     * Idempotent food log upsert on the client ULID. Macros are computed from the food/recipe.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: FoodLog, 1: bool} [log, created]
     */
    public function log(User $client, array $data): array
    {
        if (! empty($data['id'])) {
            $existing = FoodLog::withoutGlobalScopes()->withTrashed()->find($data['id']);
            if ($existing) {
                if ($existing->client_id !== $client->id || $existing->tenant_id !== app(TenantContext::class)->id()) {
                    throw ApiException::conflict('id_conflict', __('nutrition.errors.id_conflict'));
                }

                return [$existing->load(['food', 'recipe']), false];
            }
        }

        $quantity = (float) $data['quantity'];
        $unit = (string) ($data['unit'] ?? 'g');
        $attributes = [
            'id' => $data['id'] ?? (string) Str::ulid(),
            'client_id' => $client->id,
            'log_date' => $data['log_date'],
            'slot' => $data['slot'],
            'quantity' => $quantity,
            'unit' => $unit,
            'source' => $data['source'] ?? 'search',
            'meal_item_id' => $data['meal_item_id'] ?? null,
            'logged_at' => $data['logged_at'] ?? now(),
        ];

        if (! empty($data['food_id'])) {
            $food = Food::query()->findOrFail($data['food_id']);
            $attributes += ['food_id' => $food->id] + $this->macros->forFood($food, $quantity, $unit);
        } elseif (! empty($data['recipe_id'])) {
            $recipe = Recipe::query()->findOrFail($data['recipe_id']);
            $attributes += ['recipe_id' => $recipe->id] + $this->macros->forRecipe($recipe, $quantity, $unit === 'g' && empty($data['unit']) ? 'serving' : $unit);
        } else {
            if (empty($data['name']) || ! isset($data['kcal'])) {
                throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['name' => [__('nutrition.errors.quick_add_requires')]]);
            }
            $attributes += [
                'name' => $data['name'],
                'kcal' => round((float) $data['kcal'], 1),
                'protein_g' => round((float) ($data['protein_g'] ?? 0), 1),
                'carbs_g' => round((float) ($data['carbs_g'] ?? 0), 1),
                'fat_g' => round((float) ($data['fat_g'] ?? 0), 1),
            ];
            if (! empty($data['save_food']) && $quantity > 0 && $unit === 'g') {
                $this->submitFood($client, $data, $quantity);
            }
        }

        $log = FoodLog::create($attributes);

        return [$log->load(['food', 'recipe']), true];
    }

    /** Quick-adds with grams can be submitted as a tenant food, pending coach moderation. */
    private function submitFood(User $client, array $data, float $grams): Food
    {
        $f = 100 / $grams;

        return Food::create([
            'name' => [app()->getLocale() => (string) $data['name']],
            'source' => 'user',
            'kcal' => round((float) $data['kcal'] * $f, 2),
            'protein_g' => round((float) ($data['protein_g'] ?? 0) * $f, 2),
            'carbs_g' => round((float) ($data['carbs_g'] ?? 0) * $f, 2),
            'fat_g' => round((float) ($data['fat_g'] ?? 0) * $f, 2),
            'moderation' => 'pending',
            'submitted_by' => $client->id,
        ]);
    }

    /**
     * Logs every planned item of a meal slot that isn't logged yet (one tap per meal).
     *
     * @return Collection<int, FoodLog> all logs of that slot after the operation
     */
    public function fromPlan(User $client, string $date, string $slot): Collection
    {
        $plan = $this->plans->activePlanFor($client->id, $date);
        $day = $plan ? $this->plans->dayFor($plan, $date) : null;
        if (! $plan || ! $day) {
            throw new ApiException('no_active_plan', __('nutrition.errors.no_active_plan'), 404);
        }

        DB::transaction(function () use ($client, $date, $slot, $plan, $day) {
            $logs = $this->logsFor($client->id, $date);
            foreach ($this->plans->mealsFor($plan, $day, $date) as $row) {
                if ($row['slot'] !== $slot) {
                    continue;
                }
                foreach ($this->plans->plannedItems($row['meal'], $logs) as $item) {
                    if ($item['logged']) {
                        continue;
                    }
                    FoodLog::create([
                        'client_id' => $client->id,
                        'log_date' => $date,
                        'slot' => $slot,
                        'food_id' => $item['food_id'],
                        'recipe_id' => $item['recipe_id'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'],
                        'kcal' => $item['kcal'],
                        'protein_g' => $item['protein_g'],
                        'carbs_g' => $item['carbs_g'],
                        'fat_g' => $item['fat_g'],
                        'source' => 'plan',
                        'meal_item_id' => $item['meal_item_id'],
                        'logged_at' => now(),
                    ]);
                }
            }
        });

        return $this->logsFor($client->id, $date)->where('slot', $slot)->values();
    }

    /** @return Collection<int, FoodLog> the newly created logs */
    public function copyDay(User $client, string $from, string $to, ?string $slot = null): Collection
    {
        if ($from === $to) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['to_date' => [__('nutrition.errors.same_day')]]);
        }
        $source = $this->logsFor($client->id, $from)->when($slot, fn ($c) => $c->where('slot', $slot));

        return DB::transaction(fn () => $source->map(fn (FoodLog $log) => FoodLog::create([
            'client_id' => $client->id,
            'log_date' => $to,
            'slot' => $log->slot,
            'food_id' => $log->food_id,
            'recipe_id' => $log->recipe_id,
            'name' => $log->name,
            'quantity' => $log->quantity,
            'unit' => $log->unit,
            'kcal' => $log->kcal,
            'protein_g' => $log->protein_g,
            'carbs_g' => $log->carbs_g,
            'fat_g' => $log->fat_g,
            'source' => 'copy',
            'logged_at' => now(),
        ])->load(['food', 'recipe']))->values());
    }

    /** Idempotent water log; returns the day total. */
    public function water(User $client, ?string $id, string $date, int $ml): int
    {
        if ($id) {
            $existing = WaterLog::withoutGlobalScopes()->find($id);
            if ($existing && $existing->client_id !== $client->id) {
                throw ApiException::conflict('id_conflict', __('nutrition.errors.id_conflict'));
            }
        }
        if (! $id || ! isset($existing)) {
            WaterLog::create(array_filter(['id' => $id]) + ['client_id' => $client->id, 'log_date' => $date, 'ml' => $ml, 'logged_at' => now()]);
        }

        return (int) WaterLog::query()->where('client_id', $client->id)->whereDate('log_date', $date)->sum('ml');
    }

    /** @return Collection<int, Food> */
    public function recentFoods(User $client, int $limit = 30): Collection
    {
        $ids = FoodLog::query()->where('client_id', $client->id)->whereNotNull('food_id')
            ->where('log_date', '>=', now()->subDays(90)->toDateString())
            ->orderByDesc('logged_at')->limit(300)->pluck('food_id')->unique()->take($limit)->values();
        $foods = Food::query()->whereIn('id', $ids)->get()->keyBy('id');

        return $ids->map(fn ($id) => $foods[$id] ?? null)->filter()->values();
    }

    /** @return Collection<int, Food> */
    public function favoriteFoods(User $client): Collection
    {
        $ids = DB::table('food_favorites')->where('user_id', $client->id)->pluck('food_id');

        return Food::query()->whereIn('id', $ids)->orderBy('id')->get();
    }

    public function activePlan(User $client, string $date): ?MealPlan
    {
        return $this->plans->activePlanFor($client->id, $date);
    }
}
