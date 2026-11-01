<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class WaterLog extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'water_logs';

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'logged_at' => 'datetime',
        ];
    }
}
