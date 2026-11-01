<?php

namespace App\Domain\Payments\Models;

use App\Domain\Shared\Models\BaseModel;

class WebhookEvent extends BaseModel
{
    protected $table = 'webhook_events';

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
