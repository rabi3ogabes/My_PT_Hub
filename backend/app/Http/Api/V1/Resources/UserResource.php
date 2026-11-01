<?php

namespace App\Http\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Domain\Identity\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->firstName(),
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'units' => $this->units,
            'theme' => $this->theme,
            'email_verified' => $this->email_verified_at !== null,
            'two_factor_enabled' => $this->hasTwoFactorEnabled(),
            'current_tenant_id' => $this->current_tenant_id,
            'is_super_admin' => (bool) $this->is_super_admin,
            'notification_preferences' => (object) ($this->notification_preferences ?? []),
            'quiet_hours' => (object) ($this->quiet_hours ?? []),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
