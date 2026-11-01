<?php

namespace App\Domain\Integrations\Models;

use App\Domain\Shared\Models\BaseModel;

class HealthDaily extends BaseModel
{
    protected $table = 'health_daily';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'value' => 'float',
        ];
    }
}
