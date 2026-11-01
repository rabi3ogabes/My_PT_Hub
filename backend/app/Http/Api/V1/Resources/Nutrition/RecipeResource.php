<?php

namespace App\Http\Api\V1\Resources\Nutrition;

use App\Http\Api\V1\Support\ViewerContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Nutrition\Models\Recipe
 *
 * Pass `scale_servings` (int) via the request attributes to scale ingredient quantities.
 */
class RecipeResource extends JsonResource
{
    public bool $withItems = true;

    public function toArray(Request $request): array
    {
        $servings = max(1, (int) $this->servings);
        $target = (int) ($request->attributes->get('recipe.scale_servings') ?: $servings);
        $factor = $target / $servings;
        $steps = $this->translateRaw('steps');

        $data = [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'cuisine' => $this->cuisine,
            'prep_minutes' => (int) $this->prep_minutes,
            'cook_minutes' => (int) $this->cook_minutes,
            'servings' => $target,
            'tags' => array_values($this->tags ?? []),
            'photo_url' => $this->photo_url,
            'per_serving' => [
                'kcal' => (float) $this->kcal,
                'protein_g' => (float) $this->protein_g,
                'carbs_g' => (float) $this->carbs_g,
                'fat_g' => (float) $this->fat_g,
            ],
            'items' => $this->relationLoaded('items') ? $this->items->map(fn ($item) => [
                'food' => $item->food ? (new FoodResource($item->food))->resolve($request) : null,
                'grams' => round((float) $item->grams * $factor, 1),
                'display_qty' => $item->display_qty !== null ? round((float) $item->display_qty * $factor, 2) : null,
                'display_unit' => $item->display_unit,
            ])->values()->all() : [],
            'steps' => is_array($steps) ? array_values($steps) : ($steps ? [(string) $steps] : []),
        ];

        if (ViewerContext::isStaff($request)) {
            $data['name_translations'] = (object) ($this->name ?? []);
            $data['description_translations'] = (object) ($this->description ?? []);
            $data['steps_translations'] = (object) ($this->steps ?? []);
            $data['is_published'] = (bool) $this->is_published;
            $data['is_custom'] = $this->tenant_id !== null;
        }

        return $data;
    }
}
