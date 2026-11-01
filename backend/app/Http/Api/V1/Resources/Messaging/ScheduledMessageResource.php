<?php

namespace App\Http\Api\V1\Resources\Messaging;

use App\Domain\Messaging\Models\ScheduledMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ScheduledMessage */
class ScheduledMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $body = $this->body ?? [];
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'segment' => (object) ($this->segment ?? []),
            'body' => $body[$locale] ?? $body['en'] ?? (array_values($body)[0] ?? null),
            'body_translations' => (object) $body,
            'send_at' => $this->send_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'status' => $this->sent_at ? 'sent' : 'scheduled',
            'recipients_count' => (int) $this->recipients_count,
            'sender' => $this->relationLoaded('sender') ? UserSummary::make($this->sender) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
