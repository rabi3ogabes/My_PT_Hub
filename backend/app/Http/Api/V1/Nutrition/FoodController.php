<?php

namespace App\Http\Api\V1\Nutrition;

use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Services\FoodService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Nutrition\FoodResource;
use App\Http\Api\V1\Support\TranslatableInput;
use App\Http\Api\V1\Support\ViewerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FoodController extends ApiController
{
    public function __construct(private readonly FoodService $foods) {}

    /** GET /foods?q=&region=&category=&diet=&cursor= */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', Rule::in(FoodService::REGIONS)],
            'category' => ['nullable', 'string', 'max:32'],
            'diet' => ['nullable', 'string', 'max:32'],
            'source' => ['nullable', 'string', 'max:16'],
            'moderation' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $query = $this->foods->visibleQuery($this->user(), ViewerContext::isStaff($request) ? ($filters['moderation'] ?? null) : null);
        $this->foods->applySearch($query, $filters['q'] ?? null);
        $query->when($filters['region'] ?? null, fn ($q, $v) => $q->where('region', $v))
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->when($filters['source'] ?? null, fn ($q, $v) => $q->where('source', $v))
            ->when($filters['diet'] ?? null, fn ($q, $v) => $q->where('diet_tags', 'like', '%"'.str_replace(['%', '_', '"'], '', $v).'"%'))
            ->orderBy('id');

        $page = $query->cursorPaginate($this->perPage());
        $this->shareFavorites($request, collect($page->items())->pluck('id')->all());

        return $this->paginated($page, FoodResource::class);
    }

    public function show(Request $request, Food $food): JsonResponse
    {
        $this->foods->assertVisible($food, $this->user());
        $this->shareFavorites($request, [$food->id]);

        return $this->ok(new FoodResource($food));
    }

    /** GET /foods/barcode/{code}: local database first, then Open Food Facts (saved as source=off). */
    public function barcode(Request $request, string $code): JsonResponse
    {
        if (! preg_match('/^[0-9]{6,14}$/', $code)) {
            throw new ApiException('not_found', __('nutrition.errors.barcode_not_found'), 404);
        }
        $food = $this->foods->byBarcode($code, $this->user())
            ?? throw new ApiException('not_found', __('nutrition.errors.barcode_not_found'), 404);
        $this->shareFavorites($request, [$food->id]);

        return $this->ok(new FoodResource($food));
    }

    /** POST /foods — tenant custom food (pending moderation when submitted without nutrition.manage). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(true));
        $manager = $this->user()->can('nutrition.manage');

        $food = DB::transaction(fn () => Food::create($this->foods->attributes($data) + [
            'tenant_id' => $this->tenant()->id,
            'source' => 'custom',
            'moderation' => $manager ? 'approved' : 'pending',
            'submitted_by' => $this->user()->id,
            'is_verified' => false,
        ]));

        return $this->created(new FoodResource($food->refresh()));
    }

    public function update(Request $request, Food $food): JsonResponse
    {
        if (! $food->isOwnedByCurrentTenant()) {
            throw ApiException::forbidden(__('nutrition.errors.global_food_readonly'));
        }
        $manager = $this->user()->can('nutrition.manage');
        if (! $manager && $food->submitted_by !== $this->user()->id) {
            throw ApiException::forbidden();
        }
        $data = $request->validate($this->rules(false) + [
            'moderation' => ['sometimes', Rule::in(['pending', 'approved', 'rejected'])],
            'is_verified' => ['sometimes', 'boolean'],
        ]);
        $attributes = $this->foods->attributes($data, $food);
        if ($manager) {
            $attributes += array_intersect_key($data, array_flip(['moderation', 'is_verified']));
        }
        $food->update($attributes);

        return $this->ok(new FoodResource($food->refresh()));
    }

    /** @return array<string, mixed> */
    private function rules(bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';

        return [
            ...TranslatableInput::rules('name', $creating),
            'aliases' => ['nullable', 'array', 'max:20'],
            'aliases.*' => ['string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:120'],
            'barcode' => ['nullable', 'regex:/^[0-9]{6,14}$/'],
            'region' => ['nullable', Rule::in(FoodService::REGIONS)],
            'category' => ['nullable', Rule::in(FoodService::CATEGORIES)],
            'per_100g' => [$req, 'array'],
            'per_100g.kcal' => [$req, 'numeric', 'min:0', 'max:950'],
            'per_100g.protein_g' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'per_100g.carbs_g' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'per_100g.fat_g' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'per_100g.fiber_g' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'per_100g.sugar_g' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'per_100g.sodium_mg' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'servings' => ['nullable', 'array', 'max:12'],
            'servings.*.unit' => ['required', 'string', 'max:16', 'regex:/^[a-z_]+$/'],
            'servings.*.grams' => ['required', 'numeric', 'gt:0', 'max:5000'],
            'servings.*.label' => ['nullable'],
            'diet_tags' => ['nullable', 'array'],
            'diet_tags.*' => [Rule::in(FoodService::DIET_TAGS)],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', 'max:32'],
        ];
    }

    /** @param list<string> $ids */
    private function shareFavorites(Request $request, array $ids): void
    {
        ViewerContext::setFavoriteFoodIds($request, $this->foods->favoriteIds($this->user(), $ids));
    }
}
