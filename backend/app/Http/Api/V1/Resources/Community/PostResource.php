<?php

namespace App\Http\Api\V1\Resources\Community;

use App\Domain\Community\Models\Post;
use App\Http\Api\V1\Resources\Messaging\UserSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Expects CommunityService::present() to have run for the viewer.
 *
 * @mixin Post
 */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $p = $this->presentation;
        $meta = $this->meta ?? [];

        $poll = null;
        if ($this->type === 'poll') {
            $counts = $p['vote_counts'] ?? [];
            $poll = [
                'options' => array_map(fn ($label, $i) => ['index' => $i, 'label' => $label, 'votes' => (int) ($counts[$i] ?? 0)], $meta['poll']['options'] ?? [], array_keys($meta['poll']['options'] ?? [])),
                'total_votes' => (int) ($p['total_votes'] ?? 0),
                'my_vote' => $p['my_vote'] ?? null,
                'closes_at' => $meta['poll']['closes_at'] ?? null,
            ];
        }

        return [
            'id' => $this->id,
            'space_id' => $this->community_space_id,
            'author' => UserSummary::make($this->author),
            'type' => $this->type,
            'body' => $this->body,
            'media' => array_map(fn (array $m) => [
                'url' => Storage::disk('public')->url($m['path']),
                'mime' => $m['mime'],
                'size' => $m['size'] ?? null,
            ], $meta['media'] ?? []),
            'poll' => $poll,
            'workout' => $meta['workout'] ?? null,
            'is_pinned' => (bool) $this->is_pinned,
            'is_hidden' => (bool) $this->is_hidden,
            'comments_count' => (int) $this->comments_count,
            'reactions_count' => (int) $this->reactions_count,
            'my_reaction' => $p['my_reaction'] ?? null,
            'can_delete' => (bool) ($p['can_delete'] ?? false),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
