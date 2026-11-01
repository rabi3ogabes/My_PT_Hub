<?php

namespace App\Http\Api\V1\Resources;

use App\Domain\Integrations\Models\WebhookDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WebhookDelivery */
class WebhookDeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event,
            'status' => $this->status,
            'attempts' => (int) $this->attempts,
            'response_code' => $this->response_code,
            'response_body' => $this->response_body,
            'error' => $this->error,
            'duration_ms' => $this->duration_ms,
            'next_retry_at' => $this->next_retry_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'payload' => $this->payload,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
