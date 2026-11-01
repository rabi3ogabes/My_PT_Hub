<?php

namespace App\Http\Api\V1\Resources\Community;

use App\Domain\Community\Models\Comment;
use App\Http\Api\V1\Resources\Messaging\UserSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Comment */
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'author' => UserSummary::make($this->author),
            'body' => $this->body,
            'is_hidden' => (bool) $this->is_hidden,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
