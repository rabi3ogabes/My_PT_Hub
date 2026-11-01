<?php

namespace App\Http\Api\V1\Resources\Crm;

use App\Domain\Payments\Support\MoneyJson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Crm\Models\Lead */
class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currency = $this->relationLoaded('tenant') ? $this->tenant?->currency : app(\App\Domain\Shared\Tenancy\TenantContext::class)->get()?->currency;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'message' => $this->message,
            'tags' => $this->tags ?? [],
            'value' => $this->value_minor !== null && $currency ? MoneyJson::make((int) $this->value_minor, $currency) : null,
            'consent' => (bool) $this->consent,
            'stage' => $this->relationLoaded('stage') && $this->stage ? ['id' => $this->stage->id, 'name' => $this->stage->name, 'kind' => $this->stage->kind] : null,
            'owner' => $this->relationLoaded('owner') && $this->owner ? ['id' => $this->owner->id, 'name' => $this->owner->name] : null,
            'converted_user_id' => $this->converted_user_id,
            'converted_at' => $this->converted_at?->toIso8601String(),
            'activities' => $this->when($this->relationLoaded('activities'), fn () => LeadActivityResource::collection($this->activities)->resolve($request)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
