<?php

namespace App\Domain\Payments\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class PaymentGateway extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'payment_gateways';

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'currencies' => 'array',
            'is_default' => 'boolean',
            'is_live' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $hidden = ['credentials'];
}
