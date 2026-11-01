<?php

namespace App\Http\Api\V1\Resources\Habits;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Habits\Models\HabitAssignment Staff view of an assignment. */
class HabitAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'habit' => $this->relationLoaded('habit') && $this->habit ? (new HabitResource($this->habit))->resolve($request) : null,
            'client_id' => $this->client_id,
            'schedule' => $this->schedule,
            'days' => array_values(array_map('intval', $this->days ?? [])),
            'per_week' => $this->per_week !== null ? (int) $this->per_week : null,
            'target' => $this->target !== null ? (float) $this->target : null,
            'reminder_times' => array_values($this->reminder_times ?? []),
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_active' => (bool) $this->is_active,
        ];
    }
}
