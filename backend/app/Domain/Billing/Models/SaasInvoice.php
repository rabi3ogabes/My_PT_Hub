<?php

namespace App\Domain\Billing\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasInvoice extends BaseModel
{
    protected $table = 'saas_invoices';

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
