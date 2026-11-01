<?php

namespace App\Http\Api\V1\Resources\Workouts;

use App\Domain\Workouts\Models\PersonalRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PersonalRecord */
class PersonalRecordResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var PersonalRecord $r */
        $r = $this->resource;

        return [
            'id' => $r->id,
            'exercise_id' => $r->exercise_id,
            'exercise_name' => $r->relationLoaded('exercise') ? $r->exercise?->translate('name') : null,
            'type' => $r->type,
            'value' => $r->value,
            'previous_value' => $r->previous_value,
            'weight_kg' => $r->weight_kg,
            'achieved_at' => $r->achieved_at?->toIso8601String(),
        ];
    }
}
