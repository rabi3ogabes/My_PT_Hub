<?php

namespace App\Http\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Wraps a ClientProfile (with user, coach and optional `active_program` / `unread_messages` attributes).
 *
 * @mixin \App\Domain\Tenancy\Models\ClientProfile
 */
class ClientSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;

        return [
            'id' => $user->id,
            'profile_id' => $this->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null,
            'status' => $this->status,
            'goal' => $this->goal,
            'level' => $this->level,
            'tags' => $this->tags ?? [],
            'coach' => $this->coach ? ['id' => $this->coach->id, 'name' => $this->coach->name] : null,
            'compliance_score' => $this->compliance_score,
            'risk_status' => $this->risk_status,
            'last_active_at' => ($this->last_active_at ?? $user->last_active_at)?->toIso8601String(),
            'program' => $this->getAttribute('active_program'),
            'unread_messages' => (int) ($this->getAttribute('unread_messages') ?? 0),
            'joined_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
