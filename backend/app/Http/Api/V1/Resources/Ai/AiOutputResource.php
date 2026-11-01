<?php

namespace App\Http\Api\V1\Resources\Ai;

use App\Domain\Ai\Models\AiOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AiOutput */
class AiOutputResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $person = fn ($user) => $user ? ['id' => $user->id, 'name' => $user->name] : null;

        return [
            'id' => $this->id,
            'feature' => $this->feature,
            'status' => $this->status,
            'client' => $person($this->relationLoaded('client') ? $this->client : $this->client()->first()),
            'author' => $person($this->relationLoaded('author') ? $this->author : $this->author()->first()),
            'input' => (object) ($this->input ?? []),
            'payload' => (object) ($this->payload ?? []),
            'edited_payload' => $this->edited_payload,
            'edited_diff' => $this->edited_diff ?? [],
            'grounding' => (object) ($this->grounding ?? []),
            'flags' => $this->flags ?? [],
            'review_note' => $this->review_note,
            'reviewed_by' => $person($this->relationLoaded('reviewer') ? $this->reviewer : ($this->reviewed_by ? $this->reviewer()->first() : null)),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'applied' => $this->applied_type ? ['type' => $this->applied_type, 'id' => $this->applied_id] : null,
            'ai_request_id' => $this->ai_request_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
