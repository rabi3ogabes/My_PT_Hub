<?php

namespace App\Http\Api\V1\Resources\Community;

use App\Domain\Community\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Challenge */
class ChallengeResource extends JsonResource
{
    /** @var array<string, mixed> viewer data: my_entry, leaderboard */
    public array $extra = [];

    public function toArray(Request $request): array
    {
        $today = now()->toDateString();
        $status = $this->starts_on->toDateString() > $today ? 'upcoming' : ($this->ends_on->toDateString() < $today ? 'ended' : 'active');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'metric' => $this->metric,
            'metric_label' => __('community_module.metrics.'.$this->metric),
            'goal' => $this->goal,
            'starts_on' => $this->starts_on->toDateString(),
            'ends_on' => $this->ends_on->toDateString(),
            'status' => $status,
            'community_space_id' => $this->community_space_id,
            'badge_key' => $this->badge_key,
            'participants_count' => (int) ($this->entries_count ?? 0),
            'joined' => array_key_exists('my_entry', $this->extra) ? $this->extra['my_entry'] !== null : null,
            'my_entry' => $this->extra['my_entry'] ?? null,
            'leaderboard' => $this->extra['leaderboard'] ?? null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
