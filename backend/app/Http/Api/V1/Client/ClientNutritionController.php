<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Services\ClientNutritionService;
use App\Domain\Nutrition\Services\FoodService;
use App\Domain\Nutrition\Services\MealPlanService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Nutrition\FoodLogResource;
use App\Http\Api\V1\Resources\Nutrition\FoodResource;
use App\Http\Api\V1\Support\ViewerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientNutritionController extends ApiController
{
    public function __construct(
        private readonly ClientNutritionService $nutrition,
        private readonly FoodService $foods,
        private readonly MealPlanService $plans,
    ) {}

    /** GET /me/nutrition?date= */
    public function day(Request $request): JsonResponse
    {
        $date = $this->date($request);
        $day = $this->nutrition->day($this->user(), $date);
        $day['meals'] = array_map(fn (array $meal) => [
            ...$meal,
            'logs' => FoodLogResource::collection($meal['logs'])->resolve($request),
        ], $day['meals']);

        return $this->ok($day);
    }

    /** GET /me/nutrition/week?date= (7 days ending on date) */
    public function week(Request $request): JsonResponse
    {
        return $this->ok($this->nutrition->week($this->user(), $this->date($request)));
    }

    /** GET /me/nutrition/plan */
    public function plan(Request $request): JsonResponse
    {
        $date = $this->date($request);
        $plan = $this->nutrition->activePlan($this->user(), $date);
        if (! $plan) {
            return $this->ok(null);
        }

        return $this->ok($this->plans->clientPayload($plan, $date, $this->nutrition->logsFor($this->user()->id, $date)));
    }

    /** POST /me/food-logs — idempotent on client ULID `id`. */
    public function log(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'ulid'],
            'log_date' => ['required', 'date_format:Y-m-d'],
            'slot' => ['required', Rule::in(MealPlanService::SLOTS)],
            'food_id' => ['nullable', 'string', 'size:26', 'prohibits:recipe_id'],
            'recipe_id' => ['nullable', 'string', 'size:26'],
            'name' => ['nullable', 'string', 'max:190'],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'unit' => ['nullable', 'string', 'max:16'],
            'kcal' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'protein_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'carbs_g' => ['nullable', 'numeric', 'min:0', 'max:2000'],
            'fat_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'source' => ['nullable', Rule::in(['search', 'barcode', 'quick', 'photo', 'plan', 'copy'])],
            'meal_item_id' => ['nullable', 'string', 'size:26'],
            'logged_at' => ['nullable', 'date'],
            'save_food' => ['sometimes', 'boolean'],
        ]);
        [$log, $created] = $this->nutrition->log($this->user(), $data);
        $payload = (new FoodLogResource($log))->resolve($request);

        return $created ? $this->created($payload) : $this->ok($payload);
    }

    /** DELETE /me/food-logs/{foodLog} */
    public function deleteLog(FoodLog $foodLog): JsonResponse
    {
        if ($foodLog->client_id !== $this->user()->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        $foodLog->delete();

        return $this->noContent();
    }

    /** POST /me/food-logs/from-plan {log_date, slot} */
    public function fromPlan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'log_date' => ['required', 'date_format:Y-m-d'],
            'slot' => ['required', Rule::in(MealPlanService::SLOTS)],
        ]);
        $logs = $this->nutrition->fromPlan($this->user(), $data['log_date'], $data['slot']);

        return $this->ok(['logs' => FoodLogResource::collection($logs)->resolve($request)]);
    }

    /** POST /me/food-logs/copy-day {from_date, to_date, slot?} */
    public function copyDay(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_date' => ['required', 'date_format:Y-m-d'],
            'to_date' => ['required', 'date_format:Y-m-d'],
            'slot' => ['nullable', Rule::in(MealPlanService::SLOTS)],
        ]);
        $logs = $this->nutrition->copyDay($this->user(), $data['from_date'], $data['to_date'], $data['slot'] ?? null);

        return $this->created(['logs' => FoodLogResource::collection($logs)->resolve($request)]);
    }

    /** POST /me/water {id?, log_date, ml} */
    public function water(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'ulid'],
            'log_date' => ['required', 'date_format:Y-m-d'],
            'ml' => ['required', 'integer', 'min:1', 'max:5000'],
        ]);
        $total = $this->nutrition->water($this->user(), $data['id'] ?? null, $data['log_date'], (int) $data['ml']);
        $target = $this->nutrition->day($this->user(), $data['log_date'])['water_target_ml'];

        return $this->ok(['log_date' => $data['log_date'], 'water_ml' => $total, 'water_target_ml' => $target]);
    }

    /** GET /me/foods/recent (?favorites=1 for favourites) */
    public function recent(Request $request): JsonResponse
    {
        $foods = $request->boolean('favorites')
            ? $this->nutrition->favoriteFoods($this->user())
            : $this->nutrition->recentFoods($this->user());
        ViewerContext::setFavoriteFoodIds($request, $this->foods->favoriteIds($this->user(), $foods->pluck('id')->all()));

        return $this->ok(FoodResource::collection($foods)->resolve($request));
    }

    /** POST /me/foods/{food}/favorite — toggles. */
    public function favorite(Food $food): JsonResponse
    {
        $this->foods->assertVisible($food, $this->user());

        return $this->ok(['food_id' => $food->id, 'is_favorite' => $this->foods->toggleFavorite($this->user(), $food)]);
    }

    private function date(Request $request): string
    {
        $date = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']])['date'] ?? null;

        return $date ?? $this->nutrition->today($this->user());
    }
}
