<?php

namespace App\Domain\Payments\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class Coupon extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'coupons';

    protected function casts(): array
    {
        return [
            'product_ids' => 'array',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
