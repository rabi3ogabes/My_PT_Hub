<?php

namespace App\Http\Api\V1\Resources\Community;

use App\Domain\Community\Models\CommunitySpace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CommunitySpace */
class SpaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'client_group_id' => $this->client_group_id,
            'program_id' => $this->program_id,
            'announcement_only' => (bool) $this->announcement_only,
            'posts_count' => $this->posts_count !== null ? (int) $this->posts_count : null,
            'can_post' => ! $this->announcement_only || ($user && $user->isStaffIn()),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
