<?php

namespace App\Domain\Tenancy\Models;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tenants';

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'legal' => 'array',
            'trial_ends_at' => 'datetime',
            'onboarded_at' => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')->withPivot('role', 'status')->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class)->withoutGlobalScopes();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SaasSubscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(SaasSubscription::class)->latestOfMany();
    }

    public function saasPlan(): ?SaasPlan
    {
        return SaasPlan::query()->where('code', $this->plan)->first();
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    public function isPersonal(): bool
    {
        return $this->type === 'personal';
    }

    public function staffCount(): int
    {
        return $this->memberships()->whereIn('role', ['owner', 'coach', 'assistant'])->where('status', 'active')->count();
    }

    public function clientCount(): int
    {
        return $this->memberships()->where('role', 'client')->where('status', 'active')->count();
    }
}
