<?php

namespace App\Http\Api\V1\Resources\Crm;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Crm\Models\LeadStage */
class LeadStageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => (int) $this->position,
            'kind' => $this->kind,
            'leads_count' => $this->when(isset($this->leads_count), fn () => (int) $this->leads_count),
        ];
    }
}
