<?php

namespace App\Http\Api\V1\Resources\Workouts;

use App\Domain\Workouts\Models\WorkoutSetTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WorkoutSetTemplate */
class SetTemplateResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return self::shape($this->resource);
    }

    /** @return array<string, mixed> */
    public static function shape(WorkoutSetTemplate $s): array
    {
        return [
            'id' => $s->id,
            'position' => (int) $s->position,
            'set_type' => $s->set_type,
            'reps_min' => $s->reps_min !== null ? (int) $s->reps_min : null,
            'reps_max' => $s->reps_max !== null ? (int) $s->reps_max : null,
            'weight_kg' => $s->weight_kg,
            'percent_1rm' => $s->percent_1rm,
            'rpe' => $s->rpe,
            'rir' => $s->rir !== null ? (int) $s->rir : null,
            'duration_seconds' => $s->duration_seconds !== null ? (int) $s->duration_seconds : null,
            'distance_m' => $s->distance_m !== null ? (int) $s->distance_m : null,
            'rest_seconds' => $s->rest_seconds !== null ? (int) $s->rest_seconds : null,
        ];
    }
}
