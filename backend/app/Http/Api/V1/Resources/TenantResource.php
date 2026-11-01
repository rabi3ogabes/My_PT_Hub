<?php

namespace App\Http\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Domain\Tenancy\Models\Tenant */
class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_url' => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null,
            'brand_color' => $this->brand_color,
            'currency' => $this->currency,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'plan' => $this->plan,
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'onboarded' => $this->onboarded_at !== null,
            'role' => $this->whenPivotLoaded('tenant_user', fn () => $this->pivot->role),
        ];
    }
}
