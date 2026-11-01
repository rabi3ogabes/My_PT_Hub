<?php

namespace App\Http\Api\V1\Resources;

use Illuminate\Http\Request;

/** @mixin \App\Domain\Tenancy\Models\ClientProfile */
class ClientDetailResource extends ClientSummaryResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'age' => $this->age(),
            'height_cm' => $this->height_cm,
            'activity_level' => $this->activity_level,
            'injuries' => $this->injuries,
            'medical_notes' => $this->medical_notes,
            'equipment' => $this->equipment ?? [],
            'days_per_week' => $this->days_per_week,
            'diet_tags' => $this->diet_tags ?? [],
            'onboarding' => (object) ($this->onboarding ?? []),
            'parq_flagged' => (bool) $this->parq_flagged,
            'locale' => $this->user->locale,
            'timezone' => $this->user->timezone,
            'units' => $this->user->units,
        ]);
    }
}
