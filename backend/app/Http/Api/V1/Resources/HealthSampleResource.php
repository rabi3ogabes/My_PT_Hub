<?php

namespace App\Http\Api\V1\Resources;

use App\Domain\Integrations\Models\HealthSample;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HealthSample */
class HealthSampleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'value' => (float) $this->value,
            'unit' => $this->unit,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'source' => $this->source,
            'external_id' => $this->external_id,
            'meta' => (object) ($this->meta ?? []),
        ];
    }
}
