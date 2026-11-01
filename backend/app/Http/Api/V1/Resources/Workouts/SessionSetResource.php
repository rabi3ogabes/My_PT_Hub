<?php

namespace App\Http\Api\V1\Resources\Workouts;

use App\Domain\Workouts\Models\SessionSet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SessionSet */
class SessionSetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return self::shape($this->resource);
    }

    /** @return array<string, mixed> */
    public static function shape(SessionSet $s): array
    {
        return [
            'id' => $s->id,
            'exercise_id' => $s->exercise_id,
            'workout_exercise_id' => $s->workout_exercise_id,
            'set_number' => (int) $s->set_number,
            'set_type' => $s->set_type,
            'reps' => $s->reps,
            'weight_kg' => $s->weight_kg,
            'duration_seconds' => $s->duration_seconds,
            'distance_m' => $s->distance_m,
            'rpe' => $s->rpe,
            'is_completed' => (bool) $s->is_completed,
            'completed_at' => $s->completed_at?->toIso8601String(),
        ];
    }
}
