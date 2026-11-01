<?php

namespace App\Domain\Payments\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'invoices';

    protected function casts(): array
    {
        return [
            'seller' => 'array',
            'buyer' => 'array',
            'lines' => 'array',
            'issued_at' => 'datetime',
            'due_on' => 'date',
            'tax_rate' => 'float',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
