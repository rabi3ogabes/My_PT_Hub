<?php

namespace App\Domain\Calendar\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class AvailabilityRule extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'availability_rules';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_unavailable' => 'boolean',
        ];
    }
}
