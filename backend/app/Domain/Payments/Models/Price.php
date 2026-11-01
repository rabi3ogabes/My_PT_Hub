<?php

namespace App\Domain\Payments\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'prices';

    protected function casts(): array
    {
        return [
            'tax_inclusive' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
