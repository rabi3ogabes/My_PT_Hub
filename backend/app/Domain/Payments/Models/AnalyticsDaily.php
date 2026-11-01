<?php

namespace App\Domain\Payments\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class AnalyticsDaily extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'analytics_daily';

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
