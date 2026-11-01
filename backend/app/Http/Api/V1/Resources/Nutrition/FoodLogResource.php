<?php

namespace App\Http\Api\V1\Resources\Nutrition;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Nutrition\Models\FoodLog */
class FoodLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $name = $this->name;
        if ($this->food_id && $this->relationLoaded('food') && $this->food) {
            $name = $this->food->translate('name');
        } elseif ($this->recipe_id && $this->relationLoaded('recipe') && $this->recipe) {
            $name = $this->recipe->translate('name');
        }

        return [
            'id' => $this->id,
            'log_date' => $this->log_date?->toDateString(),
            'slot' => $this->slot,
            'food_id' => $this->food_id,
            'recipe_id' => $this->recipe_id,
            'name' => $name,
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'kcal' => (float) $this->kcal,
            'protein_g' => (float) $this->protein_g,
            'carbs_g' => (float) $this->carbs_g,
            'fat_g' => (float) $this->fat_g,
            'source' => $this->source,
            'logged_at' => $this->logged_at?->toIso8601String(),
        ];
    }
}
