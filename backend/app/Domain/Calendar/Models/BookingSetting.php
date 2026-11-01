<?php

namespace App\Domain\Calendar\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class BookingSetting extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'booking_settings';

    protected function casts(): array
    {
        return [
            'public_booking' => 'boolean',
        ];
    }
}
