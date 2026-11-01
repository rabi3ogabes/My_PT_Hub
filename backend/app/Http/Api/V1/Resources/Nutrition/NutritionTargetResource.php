<?php

namespace App\Http\Api\V1\Resources\Nutrition;

use App\Domain\Nutrition\Models\NutritionTarget;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NutritionTarget */
class NutritionTargetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'day_type' => $this->day_type,
            'kcal' => (int) $this->kcal,
            'protein_g' => (int) $this->protein_g,
            'carbs_g' => (int) $this->carbs_g,
            'fat_g' => (int) $this->fat_g,
            'fiber_g' => $this->fiber_g !== null ? (int) $this->fiber_g : null,
            'water_ml' => (int) $this->water_ml,
            'effective_from' => $this->effective_from?->toDateString(),
            'override_safety' => (bool) $this->override_safety,
            'calculation' => $this->calculation ? (object) $this->calculation : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
