<?php

namespace App\Http\Api\V1\Nutrition;

use App\Domain\Nutrition\Models\MealItem;
use App\Domain\Nutrition\Models\Recipe;
use App\Domain\Nutrition\Services\RecipeService;
use App\Domain\Nutrition\Support\SearchText;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Nutrition\RecipeResource;
use App\Http\Api\V1\Support\TranslatableInput;
use App\Http\Api\V1\Support\ViewerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends ApiController
{
    public function __construct(private readonly RecipeService $recipes) {}

    /** GET /recipes?q=&cuisine=&tag=&cursor= (list items omit ingredients). */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'cuisine' => ['nullable', 'string', 'max:24'],
            'tag' => ['nullable', 'string', 'max:32'],
        ]);
        $query = Recipe::query()
            ->when(! ViewerContext::isStaff($request), fn ($q) => $q->where('is_published', true))
            ->when($filters['cuisine'] ?? null, fn ($q, $v) => $q->where('cuisine', $v))
            ->when($filters['tag'] ?? null, fn ($q, $v) => $q->where('tags', 'like', '%"'.str_replace(['%', '_', '"'], '', $v).'"%'));
        $term = SearchText::normalize($filters['q'] ?? null);
        if ($term !== '') {
            foreach (array_slice(explode(' ', $term), 0, 6) as $word) {
                $query->where('search_text', 'like', SearchText::likeTerm($word));
            }
        }

        return $this->paginated($query->orderBy('id')->cursorPaginate($this->perPage()), RecipeResource::class);
    }

    /** GET /recipes/{recipe}?servings=N scales ingredient quantities. */
    public function show(Request $request, Recipe $recipe): JsonResponse
    {
        if (! $recipe->is_published && ! ViewerContext::isStaff($request)) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        $scale = $request->validate(['servings' => ['nullable', 'integer', 'min:1', 'max:100']])['servings'] ?? null;
        $request->attributes->set('recipe.scale_servings', $scale);

        return $this->ok(new RecipeResource($recipe->load('items.food')));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage();
        $recipe = $this->recipes->save($request->validate($this->rules(true)));

        return $this->created(new RecipeResource($recipe->load('items.food')));
    }

    public function update(Request $request, Recipe $recipe): JsonResponse
    {
        $this->authorizeOwned($recipe);
        $recipe = $this->recipes->save($request->validate($this->rules(false)), $recipe);

        return $this->ok(new RecipeResource($recipe->load('items.food')));
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        $this->authorizeOwned($recipe);
        $inUse = MealItem::query()->where('recipe_id', $recipe->id)
            ->whereHas('meal.day.plan', fn ($q) => $q->where('status', 'active'))
            ->exists();
        if ($inUse) {
            throw ApiException::conflict('recipe_in_use', __('nutrition.errors.recipe_in_use'));
        }
        $recipe->delete();

        return $this->noContent();
    }

    private function authorizeManage(): void
    {
        if (! $this->user()->can('nutrition.manage')) {
            throw ApiException::forbidden();
        }
    }

    private function authorizeOwned(Recipe $recipe): void
    {
        $this->authorizeManage();
        if (! $recipe->isOwnedByCurrentTenant()) {
            throw ApiException::forbidden(__('nutrition.errors.global_recipe_readonly'));
        }
    }

    /** @return array<string, mixed> */
    private function rules(bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';

        return [
            ...TranslatableInput::rules('name', $creating),
            ...TranslatableInput::rules('description', false, 2000),
            'steps' => ['nullable', 'array'],
            'cuisine' => ['nullable', 'string', 'max:24'],
            'prep_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'cook_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'servings' => [$req, 'integer', 'min:1', 'max:100'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:32'],
            'photo_url' => ['nullable', 'url', 'max:500'],
            'is_published' => ['sometimes', 'boolean'],
            'items' => [$req, 'array', 'min:1', 'max:60'],
            'items.*.food_id' => ['required', 'string', 'size:26'],
            'items.*.grams' => ['required', 'numeric', 'gt:0', 'max:20000'],
            'items.*.display_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.display_unit' => ['nullable', 'string', 'max:16'],
        ];
    }
}
