<?php

namespace App\Http\Api\V1\Resources\Crm;

use App\Domain\Crm\Models\LeadActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LeadActivity */
class LeadActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'body' => $this->body,
            'user' => $this->relationLoaded('user') && $this->user ? ['id' => $this->user->id, 'name' => $this->user->name] : null,
            'due_at' => $this->due_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
