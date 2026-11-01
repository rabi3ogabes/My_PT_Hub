<?php

namespace App\Domain\Integrations\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends BaseModel
{
    protected $table = 'webhook_deliveries';

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'next_retry_at' => 'datetime',
            'delivered_at' => 'datetime',
            'attempts' => 'integer',
            'response_code' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
