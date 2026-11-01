<?php

namespace App\Http\Api\V1\Nutrition;

use App\Domain\Nutrition\Models\MealItem;
use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Nutrition\Services\MealPlanPdf;
use App\Domain\Nutrition\Services\MealPlanService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Nutrition\FoodResource;
use App\Http\Api\V1\Resources\Nutrition\MealPlanResource;
use App\Http\Api\V1\Support\TenantClients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class MealPlanController extends ApiController
{
    public function __construct(private readonly MealPlanService $plans) {}

    /** GET /meal-plans?client_id=&status=&template=1&q=&cursor= */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'client_id' => ['nullable', 'string', 'size:26'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'archived'])],
            'template' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $page = MealPlan::query()
            ->with('client')
            ->when($filters['client_id'] ?? null, fn ($q, $v) => $q->where('client_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when(array_key_exists('template', $filters) && $filters['template'] !== null, fn ($q) => $q->where('is_template', (bool) $filters['template']))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('name', 'like', '%'.str_replace(['%', '_'], '', $v).'%'))
            ->orderByDesc('id')
            ->cursorPaginate($this->perPage());

        return $this->paginated($page, MealPlanResource::class);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate($this->rules(true));
        if (! empty($data['client_id'])) {
            TenantClients::find([$data['client_id']], 'client_id');
        }

        return $this->created(new MealPlanResource($this->plans->save($data, null, $this->user())));
    }

    public function show(MealPlan $mealPlan): JsonResponse
    {
        return $this->ok(new MealPlanResource($mealPlan->load(MealPlanService::WITH)));
    }

    public function update(Request $request, MealPlan $mealPlan): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate($this->rules(false));
        if (! empty($data['client_id'])) {
            TenantClients::find([$data['client_id']], 'client_id');
        }

        return $this->ok(new MealPlanResource($this->plans->save($data, $mealPlan, $this->user())));
    }

    public function destroy(MealPlan $mealPlan): JsonResponse
    {
        $this->authorizeManage();
        $mealPlan->delete();

        return $this->noContent();
    }

    /** POST /meal-plans/{id}/assign {client_ids: [], start_date?} → each client gets an active copy. */
    public function assign(Request $request, MealPlan $mealPlan): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate([
            'client_ids' => ['required', 'array', 'min:1', 'max:200'],
            'client_ids.*' => ['string', 'size:26'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $clients = TenantClients::find($data['client_ids']);
        $plans = $this->plans->assign($mealPlan, $clients, $data['start_date'] ?? null, $this->user());

        return $this->created(MealPlanResource::collection($plans)->resolve($request));
    }

    /** GET /meal-plans/{id}/grocery-list?repeat=1 */
    public function groceryList(Request $request, MealPlan $mealPlan): JsonResponse
    {
        $repeat = (int) ($request->validate(['repeat' => ['nullable', 'integer', 'min:1', 'max:8']])['repeat'] ?? 1);

        return $this->ok(['plan_id' => $mealPlan->id, 'aisles' => $this->plans->groceryList($mealPlan, $repeat)]);
    }

    /** GET /meal-plans/{id}/pdf — printable plan (Arabic RTL supported). */
    public function pdf(MealPlan $mealPlan, MealPlanPdf $pdf): Response
    {
        $mealPlan->load(MealPlanService::WITH);
        $content = $pdf->render($mealPlan, app()->getLocale());
        $filename = str($mealPlan->name)->slug()->append('.pdf')->toString();

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.($filename === '.pdf' ? 'meal-plan.pdf' : $filename).'"',
        ]);
    }

    /**
     * POST /meal-plans/{id}/items/{item}/swaps — returns swap candidates (±10% kcal, similar macros);
     * with {food_id|recipe_id, quantity, unit} applies the swap.
     */
    public function swaps(Request $request, MealPlan $mealPlan, MealItem $mealItem): JsonResponse
    {
        $mealItem->loadMissing('meal.day');
        if ($mealItem->meal?->day?->meal_plan_id !== $mealPlan->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        $data = $request->validate([
            'food_id' => ['nullable', 'string', 'size:26', 'prohibits:recipe_id'],
            'recipe_id' => ['nullable', 'string', 'size:26'],
            'quantity' => ['nullable', 'required_with:food_id,recipe_id', 'numeric', 'gt:0', 'max:5000'],
            'unit' => ['nullable', 'string', 'max:16'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        if (! empty($data['food_id']) || ! empty($data['recipe_id'])) {
            $this->authorizeManage();
            $item = $this->plans->applySwap($mealItem, $data['food_id'] ?? null, $data['recipe_id'] ?? null, (float) $data['quantity'], $data['unit'] ?? (! empty($data['food_id']) ? 'g' : 'serving'));

            return $this->ok(new MealPlanResource($mealPlan->refresh()->load(MealPlanService::WITH)), ['swapped_item_id' => $item->id]);
        }

        $candidates = array_map(fn (array $row) => [
            ...$row,
            'food' => $row['food'] ? (new FoodResource($row['food']))->resolve($request) : null,
        ], $this->plans->swaps($mealPlan, $mealItem, (int) ($data['limit'] ?? 10)));

        return $this->ok($candidates);
    }

    private function authorizeManage(): void
    {
        if (! $this->user()->can('nutrition.manage')) {
            throw ApiException::forbidden();
        }
    }

    /** @return array<string, mixed> */
    private function rules(bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';

        return [
            'name' => [$req, 'string', 'max:190'],
            'type' => [$req, Rule::in(MealPlanService::TYPES)],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'archived'])],
            'diet_tags' => ['nullable', 'array'],
            'diet_tags.*' => ['string', 'max:24'],
            'is_template' => ['sometimes', 'boolean'],
            'client_id' => ['nullable', 'string', 'size:26'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'fasting' => ['nullable', 'array'],
            'fasting.city' => ['nullable', 'string', 'max:32'],
            'fasting.lat' => ['nullable', 'numeric', 'between:-66,66', 'required_with:fasting.lng'],
            'fasting.lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:fasting.lat'],
            'fasting.timezone' => ['nullable', 'timezone:all'],
            'fasting.window_start' => ['nullable', 'date_format:H:i'],
            'fasting.window_end' => ['nullable', 'date_format:H:i'],
            'days' => [$creating ? 'present' : 'sometimes', 'array', 'max:31'],
            'days.*.day_number' => ['nullable', 'integer', 'min:1', 'max:31'],
            'days.*.label' => ['nullable', 'string', 'max:64'],
            'days.*.day_type' => ['nullable', Rule::in(['all', 'training', 'rest'])],
            'days.*.meals' => ['nullable', 'array', 'max:12'],
            'days.*.meals.*.slot' => ['required', Rule::in(MealPlanService::SLOTS)],
            'days.*.meals.*.name' => ['nullable', 'string', 'max:64'],
            'days.*.meals.*.time' => ['nullable', 'date_format:H:i'],
            'days.*.meals.*.position' => ['nullable', 'integer', 'min:0', 'max:50'],
            'days.*.meals.*.items' => ['nullable', 'array', 'max:40'],
            'days.*.meals.*.items.*.food_id' => ['nullable', 'string', 'size:26', 'required_without:days.*.meals.*.items.*.recipe_id'],
            'days.*.meals.*.items.*.recipe_id' => ['nullable', 'string', 'size:26'],
            'days.*.meals.*.items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:5000'],
            'days.*.meals.*.items.*.unit' => ['nullable', 'string', 'max:16'],
        ];
    }
}
