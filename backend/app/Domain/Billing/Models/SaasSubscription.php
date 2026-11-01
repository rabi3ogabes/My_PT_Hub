<?php

namespace App\Domain\Billing\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasSubscription extends BaseModel
{
    protected $table = 'saas_subscriptions';

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancel_at_period_end' => 'boolean',
            'cancelled_at' => 'datetime',
            'reminder_7d_sent_at' => 'datetime',
            'reminder_1d_sent_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SaasInvoice::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trialing', 'active', 'past_due'], true);
    }

    public function onTrial(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at?->isFuture();
    }
}
