<?php

namespace App\Http\Api\V1\Resources\Messaging;

use App\Domain\Messaging\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MessageTemplate */
class MessageTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'body' => $this->translate('body'),
            'body_translations' => (object) ($this->body ?? []),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
