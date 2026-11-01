<?php

namespace App\Domain\Progress\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class BodyMetric extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'body_metrics';

    protected function casts(): array
    {
        return [
            'measured_on' => 'date',
            'weight_kg' => 'float',
            'body_fat_pct' => 'float',
            'measurements' => 'array',
        ];
    }
}
