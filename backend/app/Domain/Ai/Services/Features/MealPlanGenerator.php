<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\ClientDataService;
use App\Domain\Ai\Services\MacroBalancer;
use App\Domain\Ai\Services\SafetyGuard;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Ai\Support\SchemaValidator;
use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\Meal;
use App\Domain\Nutrition\Models\MealItem;
use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Nutrition\Models\MealPlanDay;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Nutrition\Models\Recipe;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;

/**
 * C. Meal plan generator. The model composes days/meals from the tenant's
 * food & recipe DB (filtered by diet tags, allergies and cuisine); the server
 * then VERIFIES macros: portions are scaled deterministically (MacroBalancer)
 * until every day is within ±5% of kcal/protein/carbs/fat targets. A day that
 * still misses is fed back to the model for regeneration. Kcal floors
 * (SafetyGuard) are enforced on the targets unless the coach set an override.
 */
final class MealPlanGenerator
{
    public const CUISINE_REGIONS = [
        'gulf' => 'gulf',
        'levantine' => 'levant',
        'maghreb' => 'maghreb',
        'french' => 'french',
        'international' => 'international',
    ];

    private const DEFAULT_TIMES = [
        'breakfast' => '08:00', 'lunch' => '13:00', 'snack' => '16:30', 'dinner' => '19:30',
        'suhoor' => '03:30', 'iftar' => '18:00',
    ];

    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly ClientDataService $clients,
        private readonly MacroBalancer $balancer,
        private readonly SafetyGuard $guard,
        private readonly SchemaValidator $validator,
    ) {}

    /** @param array<string, mixed> $input */
    public function generate(Tenant $tenant, User $coach, array $input): AiOutput
    {
        $client = ! empty($input['client_id']) ? $this->clients->resolveClient($tenant, $input['client_id']) : null;
        $profile = $client ? $this->clients->profile($tenant, $client) : null;
        $storedTarget = $client ? NutritionTarget::query()->where('client_id', $client->id)->orderByDesc('effective_from')->first() : null;

        $targets = $this->targets($input, $storedTarget);
        $flags = [];

        [$kcal, $floorApplied] = $this->guard->enforceKcal((int) $targets['kcal'], $profile?->gender, (bool) $storedTarget?->override_safety);
        if ($floorApplied) {
            $factor = $kcal / $targets['kcal'];
            $targets = [
                'kcal' => $kcal,
                'protein_g' => (int) round($targets['protein_g'] * $factor),
                'carbs_g' => (int) round($targets['carbs_g'] * $factor),
                'fat_g' => (int) round($targets['fat_g'] * $factor),
            ];
            $flags[] = ['type' => 'kcal_floor_applied', 'severity' => 'high', 'source' => 'safety_guard', 'floor' => $kcal];
        }

        $safety = $this->guard->screenText((string) ($input['notes'] ?? ''))->merge($this->guard->screenProfile($profile));
        array_push($flags, ...$safety->toFlags());

        $ramadan = (bool) ($input['ramadan'] ?? false);
        $cuisine = $input['cuisine'] ?? 'international';
        $dietTags = array_values(array_unique([...($input['diet_tags'] ?? []), ...($profile?->diet_tags ?? [])]));
        $allergies = array_values(array_map('strtolower', $input['allergies'] ?? []));
        $days = (int) ($input['days'] ?? 3);
        $mealsPerDay = $ramadan ? 3 : (int) ($input['meals_per_day'] ?? 4);
        $language = $this->clients->language($client, $input['language'] ?? null, $tenant);

        [$foods, $recipes] = $this->candidates($dietTags, $allergies, $cuisine, $language);
        if (count($foods) < 3) {
            throw ApiException::invalid('food_library_empty', __('ai.errors.food_library_empty'));
        }
        $slots = $ramadan ? ['suhoor', 'iftar', 'snack'] : $this->slots($mealsPerDay);

        $result = $this->gateway->run(new AiTask(
            feature: 'meal_plan',
            tenant: $tenant,
            user: $coach,
            variables: [
                'targets' => $targets,
                'days' => $days,
                'slots' => $slots,
                'cuisine' => $cuisine,
                'diet_tags' => $dietTags,
                'allergies' => $allergies,
                'ramadan' => $ramadan,
                'language' => $language,
                'notes' => $input['notes'] ?? '-',
                'foods' => array_map(fn ($f) => array_diff_key($f, ['per' => 1]), array_values($foods)),
                'recipes' => array_map(fn ($r) => array_diff_key($r, ['per' => 1]), array_values($recipes)),
            ],
            context: [
                'targets' => $targets, 'days' => $days, 'slots' => $slots, 'ramadan' => $ramadan, 'language' => $language,
                'cuisine' => $cuisine, 'foods' => array_values($foods), 'name' => $input['name'] ?? null,
            ],
            schema: Schemas::mealPlan(),
            postProcess: fn (array $data) => $this->verify($data, $targets, $foods, $recipes, $slots, $days),
        ));

        $data = $result->data;
        if (! empty($input['name'])) {
            $data['name'] = $input['name'];
        }

        return $this->outputs->draft(
            'meal_plan',
            $coach,
            $client,
            array_filter(['client_id' => $client?->id, 'targets' => $targets, 'cuisine' => $cuisine, 'diet_tags' => $dietTags, 'allergies' => $allergies, 'ramadan' => $ramadan, 'days' => $days, 'meals_per_day' => $mealsPerDay, 'notes' => $input['notes'] ?? null], fn ($v) => $v !== null),
            $data + [
                'targets' => $targets,
                'ramadan' => $ramadan,
                'cuisine' => $cuisine,
                'language' => $language,
                'grocery_list' => $this->groceryList($data, $foods, $recipes),
            ],
            $this->clients->grounding($tenant, $client, ['foods_considered' => count($foods), 'recipes_considered' => count($recipes)]),
            $flags,
            $result->requestId,
        );
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{kcal: int, protein_g: int, carbs_g: int, fat_g: int}
     */
    private function targets(array $input, ?NutritionTarget $stored): array
    {
        $t = [
            'kcal' => $input['kcal'] ?? $stored?->kcal,
            'protein_g' => $input['protein_g'] ?? $stored?->protein_g,
            'carbs_g' => $input['carbs_g'] ?? $stored?->carbs_g,
            'fat_g' => $input['fat_g'] ?? $stored?->fat_g,
        ];
        if (in_array(null, $t, true)) {
            throw ApiException::invalid('targets_required', __('ai.errors.targets_required'));
        }
        $t = array_map('intval', $t);
        $fromMacros = $t['protein_g'] * 4 + $t['carbs_g'] * 4 + $t['fat_g'] * 9;
        if ($t['kcal'] <= 0 || abs($fromMacros - $t['kcal']) / $t['kcal'] > 0.08) {
            throw ApiException::invalid('targets_inconsistent', __('ai.errors.targets_inconsistent', ['kcal' => $fromMacros]));
        }

        return $t;
    }

    /** @return list<string> */
    private function slots(int $meals): array
    {
        return match (true) {
            $meals <= 3 => ['breakfast', 'lunch', 'dinner'],
            $meals === 4 => ['breakfast', 'lunch', 'snack', 'dinner'],
            $meals === 5 => ['breakfast', 'snack', 'lunch', 'snack', 'dinner'],
            default => ['breakfast', 'snack', 'lunch', 'snack', 'dinner', 'snack'],
        };
    }

    /**
     * Foods and recipes the plan may use, keyed by id, with per-unit macros.
     *
     * @param  list<string>  $dietTags
     * @param  list<string>  $allergies
     * @return array{0: array<string, array<string, mixed>>, 1: array<string, array<string, mixed>>}
     */
    public function candidates(array $dietTags, array $allergies, string $cuisine, string $language): array
    {
        $region = self::CUISINE_REGIONS[$cuisine] ?? 'international';

        $ok = function (array $tags, array $allergens, string $name) use ($dietTags, $allergies): bool {
            if (array_diff($dietTags, $tags) !== []) {
                return false;
            }
            $allergens = array_map('strtolower', $allergens);
            foreach ($allergies as $a) {
                if (in_array($a, $allergens, true) || str_contains(mb_strtolower($name), $a)) {
                    return false;
                }
            }

            return true;
        };

        $foods = Food::query()
            ->where('moderation', 'approved')
            ->where(fn ($q) => $q->whereIn('region', [$region, 'international'])->orWhereNull('region'))
            ->limit(800)
            ->get()
            ->filter(fn (Food $f) => $f->kcal > 0 && $ok($f->diet_tags ?? [], $f->allergens ?? [], (string) $f->translate('name', 'en')))
            ->sortByDesc(fn (Food $f) => ($f->region === $region ? 2 : 0) + ($f->is_verified ? 1 : 0));

        $grouped = ['protein' => [], 'carb' => [], 'fat' => []];
        foreach ($foods as $f) {
            $per = ['kcal' => $f->kcal / 100, 'protein_g' => $f->protein_g / 100, 'carbs_g' => $f->carbs_g / 100, 'fat_g' => $f->fat_g / 100];
            $group = MacroBalancer::group($per);
            if (count($grouped[$group]) >= ($group === 'fat' ? 15 : 30)) {
                continue;
            }
            $grouped[$group][$f->id] = [
                'id' => $f->id,
                'name' => (string) ($f->translate('name', $language) ?? $f->translate('name', 'en')),
                'group' => $group,
                'category' => $f->category,
                'per_100g' => ['kcal' => $f->kcal, 'protein_g' => $f->protein_g, 'carbs_g' => $f->carbs_g, 'fat_g' => $f->fat_g],
                'per' => $per,
            ];
        }
        $foodMap = $grouped['protein'] + $grouped['carb'] + $grouped['fat'];

        $recipes = Recipe::query()
            ->where('is_published', true)
            ->when($cuisine !== 'international', fn ($q) => $q->where('cuisine', $cuisine))
            ->limit(60)
            ->get()
            ->filter(fn (Recipe $r) => $r->kcal > 0 && $ok($r->tags ?? [], [], (string) $r->translate('name', 'en')))
            ->take(15);
        $recipeMap = [];
        foreach ($recipes as $r) {
            $per = ['kcal' => $r->kcal, 'protein_g' => $r->protein_g, 'carbs_g' => $r->carbs_g, 'fat_g' => $r->fat_g];
            $recipeMap[$r->id] = [
                'id' => $r->id,
                'name' => (string) ($r->translate('name', $language) ?? $r->translate('name', 'en')),
                'group' => MacroBalancer::group($per),
                'per_serving' => $per,
                'per' => $per,
            ];
        }

        return [$foodMap, $recipeMap];
    }

    /**
     * Verifies ids and slots, balances portions per day and reports days that
     * cannot reach the targets.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, int>  $targets
     * @param  array<string, array<string, mixed>>  $foods
     * @param  array<string, array<string, mixed>>  $recipes
     * @param  list<string>  $slots
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    public function verify(array $data, array $targets, array $foods, array $recipes, array $slots, int $expectedDays): array
    {
        $errors = [];
        $tolerance = (float) config('ai.meal_plan.tolerance', 0.05);
        if (count($data['days']) < $expectedDays) {
            $errors[] = "days: expected {$expectedDays} days, got ".count($data['days']);
        }

        foreach ($data['days'] as $d => &$day) {
            $flat = [];
            foreach ($day['meals'] as $m => &$meal) {
                if (! in_array($meal['slot'], $slots, true)) {
                    $errors[] = "days[{$d}].meals[{$m}].slot '{$meal['slot']}' is not allowed; use: ".implode(', ', array_unique($slots));
                }
                $meal['time'] = $meal['time'] && preg_match('/^\d{2}:\d{2}$/', $meal['time']) ? $meal['time'] : (self::DEFAULT_TIMES[$meal['slot']] ?? null);
                foreach ($meal['items'] as $i => &$item) {
                    $path = "days[{$d}].meals[{$m}].items[{$i}]";
                    if (($item['food_id'] === null) === ($item['recipe_id'] === null)) {
                        $errors[] = "{$path}: set exactly one of food_id or recipe_id";

                        continue;
                    }
                    if ($item['food_id'] !== null && ! isset($foods[$item['food_id']])) {
                        $errors[] = "{$path}.food_id '{$item['food_id']}' is not in the provided foods list";

                        continue;
                    }
                    if ($item['recipe_id'] !== null && ! isset($recipes[$item['recipe_id']])) {
                        $errors[] = "{$path}.recipe_id '{$item['recipe_id']}' is not in the provided recipes list";

                        continue;
                    }
                    $item['unit'] = $item['food_id'] !== null ? 'g' : 'serving';
                    $ref = $item['food_id'] !== null ? $foods[$item['food_id']] : $recipes[$item['recipe_id']];
                    $flat[] = ['m' => $m, 'i' => $i, 'quantity' => (float) $item['quantity'], 'unit' => $item['unit'], 'per' => $ref['per']];
                }
                unset($item);
            }
            unset($meal);

            if ($errors !== [] || $flat === []) {
                continue;
            }

            $flat = $this->ensureGroups($flat, $day, $foods);
            $balanced = $this->balancer->balance($flat, $targets, $tolerance);
            if (! $balanced['ok']) {
                $dev = collect($balanced['deviations'])->map(fn ($v, $k) => $k.' '.($v >= 0 ? '+' : '').round($v * 100, 1).'%')->implode(', ');
                $errors[] = "days[{$d}] cannot reach the targets within ±".($tolerance * 100)."% ({$dev}); include a lean protein, a starchy carb and a fat source in the main meals";

                continue;
            }
            foreach ($balanced['items'] as $row) {
                $day['meals'][$row['m']]['items'][$row['i']]['quantity'] = $row['quantity'];
            }
            foreach ($day['meals'] as &$meal) {
                foreach ($meal['items'] as &$item) {
                    $ref = $item['food_id'] !== null ? $foods[$item['food_id']] : $recipes[$item['recipe_id']];
                    $item['name'] = $ref['name'];
                    foreach (MacroBalancer::METRICS as $metric) {
                        $item[$metric] = round($ref['per'][$metric] * $item['quantity'], 1);
                    }
                }
                unset($item);
            }
            unset($meal);
            $day['totals'] = $balanced['totals'];
            $day['deviations'] = $balanced['deviations'];
        }
        unset($day);

        return [$data, $errors];
    }

    /**
     * Adds a missing macro source (e.g. no fat-dominant food in the day) so the
     * balancer has one lever per macro — the deterministic "regenerate" step.
     *
     * @param  list<array<string, mixed>>  $flat
     * @param  array<string, mixed>  $day
     * @param  array<string, array<string, mixed>>  $foods
     * @return list<array<string, mixed>>
     */
    private function ensureGroups(array $flat, array &$day, array $foods): array
    {
        $present = array_unique(array_map(fn ($r) => MacroBalancer::group($r['per']), $flat));
        foreach (['protein', 'carb', 'fat'] as $group) {
            if (in_array($group, $present, true)) {
                continue;
            }
            $food = collect($foods)->firstWhere('group', $group);
            if (! $food) {
                continue;
            }
            // Attach to the largest meal.
            $m = collect($day['meals'])->map(fn ($meal, $k) => ['k' => $k, 'n' => count($meal['items'])])->sortByDesc('n')->first()['k'];
            $quantity = $group === 'fat' ? 10.0 : 100.0;
            $day['meals'][$m]['items'][] = ['food_id' => $food['id'], 'recipe_id' => null, 'quantity' => $quantity, 'unit' => 'g'];
            $flat[] = ['m' => $m, 'i' => count($day['meals'][$m]['items']) - 1, 'quantity' => $quantity, 'unit' => 'g', 'per' => $food['per']];
        }

        return $flat;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, array<string, mixed>>  $foods
     * @param  array<string, array<string, mixed>>  $recipes
     * @return list<array<string, mixed>>
     */
    private function groceryList(array $data, array $foods, array $recipes): array
    {
        $totals = [];
        foreach ($data['days'] as $day) {
            foreach ($day['meals'] as $meal) {
                foreach ($meal['items'] as $item) {
                    $key = $item['food_id'] ?? 'r:'.$item['recipe_id'];
                    $ref = $item['food_id'] ? ($foods[$item['food_id']] ?? null) : ($recipes[$item['recipe_id']] ?? null);
                    $totals[$key] ??= [
                        'food_id' => $item['food_id'],
                        'recipe_id' => $item['recipe_id'],
                        'name' => $ref['name'] ?? '',
                        'category' => $ref['category'] ?? 'prepared',
                        'quantity' => 0.0,
                        'unit' => $item['unit'],
                    ];
                    $totals[$key]['quantity'] += $item['quantity'];
                }
            }
        }

        return collect($totals)->sortBy([['category', 'asc'], ['name', 'asc']])->values()->all();
    }

    /**
     * @param  array<string, mixed>  $edited
     * @return list<string>
     */
    public function validateEdited(AiOutput $output, array $edited): array
    {
        $errors = [];
        foreach ($edited['days'] ?? [] as $d => $day) {
            foreach ($day['meals'] ?? [] as $m => $meal) {
                if (! in_array($meal['slot'] ?? null, Schemas::SLOTS, true)) {
                    $errors[] = "days[{$d}].meals[{$m}].slot is invalid";
                }
                foreach ($meal['items'] ?? [] as $i => $item) {
                    $foodId = $item['food_id'] ?? null;
                    $recipeId = $item['recipe_id'] ?? null;
                    $exists = $foodId ? Food::query()->whereKey($foodId)->exists() : ($recipeId && Recipe::query()->whereKey($recipeId)->exists());
                    if (! $exists || (float) ($item['quantity'] ?? 0) <= 0) {
                        $errors[] = "days[{$d}].meals[{$m}].items[{$i}] references an unknown food/recipe or has no quantity";
                    }
                }
            }
        }
        if (empty($edited['days'])) {
            $errors[] = 'days: at least one day is required';
        }

        return $errors;
    }

    public function apply(AiOutput $output, User $reviewer): MealPlan
    {
        $data = $output->effectivePayload();
        $errors = $this->validateEdited($output, $data);
        if ($errors !== []) {
            throw ApiException::invalid('validation_failed', __('ai.errors.edit_invalid'), ['payload' => array_slice($errors, 0, 10)]);
        }
        $ramadan = (bool) ($output->payload['ramadan'] ?? false);

        $plan = MealPlan::query()->create([
            'created_by' => $reviewer->id,
            'client_id' => $output->client_id,
            'name' => $data['name'] ?? __('ai.meal_plan.default_name'),
            'type' => $ramadan ? 'ramadan' : 'fixed',
            'diet_tags' => $output->input['diet_tags'] ?? [],
            'is_template' => $output->client_id === null,
            'fasting' => $ramadan ? ['mode' => 'ramadan'] : null,
            'status' => 'draft',
        ]);

        foreach (array_values($data['days']) as $day) {
            $dayModel = MealPlanDay::query()->create([
                'meal_plan_id' => $plan->id,
                'day_number' => $day['day_number'],
                'label' => $day['label'] ?? null,
            ]);
            foreach (array_values($day['meals']) as $mi => $meal) {
                $mealModel = Meal::query()->create([
                    'meal_plan_day_id' => $dayModel->id,
                    'slot' => $meal['slot'],
                    'name' => $meal['name'] ?? null,
                    'time' => $meal['time'] ?? null,
                    'position' => $mi,
                ]);
                foreach (array_values($meal['items']) as $ii => $item) {
                    MealItem::query()->create([
                        'meal_id' => $mealModel->id,
                        'food_id' => $item['food_id'] ?? null,
                        'recipe_id' => $item['recipe_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit' => ! empty($item['recipe_id']) ? 'serving' : 'g',
                        'position' => $ii,
                    ]);
                }
            }
        }

        return $plan;
    }
}
