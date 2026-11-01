<?php

namespace App\Domain\Integrations\Models;

use App\Domain\Shared\Models\BaseModel;

class HealthSample extends BaseModel
{
    protected $table = 'health_samples';

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}
