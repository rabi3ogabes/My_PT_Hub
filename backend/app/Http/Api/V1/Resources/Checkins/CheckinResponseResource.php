<?php

namespace App\Http\Api\V1\Resources\Checkins;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Checkins\Models\CheckinResponse List item (client + coach inbox). */
class CheckinResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'form_name' => $this->relationLoaded('form') ? $this->form?->name : null,
            'due_on' => $this->due_on?->toDateString(),
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
        ];
        if ($this->relationLoaded('client') && $this->client) {
            $data['client'] = ['id' => $this->client->id, 'name' => $this->client->name, 'avatar_url' => null];
        }
        if (isset($this->comments_count)) {
            $data['comments_count'] = (int) $this->comments_count;
        }

        return $data;
    }

    /**
     * @return list<array{id: string, user: array{id: string, name: string}|null, body: string, created_at: string|null}>
     */
    public static function comments(iterable $comments): array
    {
        $out = [];
        foreach ($comments as $c) {
            $out[] = [
                'id' => $c->id,
                'user' => $c->user ? ['id' => $c->user->id, 'name' => $c->user->name] : null,
                'body' => $c->body,
                'created_at' => $c->created_at?->toIso8601String(),
            ];
        }

        return $out;
    }
}
