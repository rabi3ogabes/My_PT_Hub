<?php

namespace App\Http\Api\V1\Resources\Calendar;

use App\Domain\Calendar\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'timezone' => $this->timezone,
            'capacity' => (int) $this->capacity,
            'booked_count' => $this->isOccurrence ? 0 : (int) ($this->booked_count ?? 0),
            'waitlist_count' => $this->isOccurrence ? 0 : (int) ($this->waitlist_count ?? 0),
            'credits_cost' => (int) $this->credits_cost,
            'coach' => $this->relationLoaded('coach') && $this->coach ? ['id' => $this->coach->id, 'name' => $this->coach->name] : ['id' => $this->coach_id, 'name' => null],
            'location' => $this->relationLoaded('location') && $this->location ? ['id' => $this->location->id, 'name' => $this->location->name] : null,
            'meeting_url' => $this->meeting_url,
            'rrule' => $this->rrule,
            'status' => $this->status,
            // Recurrence helpers: occurrences of a series share the master id; pass
            // occurrence_starts_at (= starts_at) when booking or editing one of them.
            'series_id' => $this->isOccurrence ? $this->id : $this->series_id,
            'is_occurrence' => $this->isOccurrence,
            'occurrence_starts_at' => $this->isOccurrence ? $this->starts_at?->toIso8601String() : $this->recurrence_id?->toIso8601String(),
        ];
    }
}
