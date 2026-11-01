<?php

namespace App\Domain\Identity\Models;

use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUlids, Notifiable, SoftDeletes;

    public const ROLES = ['owner', 'coach', 'assistant', 'client'];

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'locale', 'timezone', 'units', 'theme',
        'avatar_path', 'current_tenant_id', 'notification_preferences', 'quiet_hours',
        'apple_id', 'google_id', 'email_verified_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
        'apple_id', 'google_id', 'failed_logins', 'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'notification_preferences' => 'array',
            'quiet_hours' => 'array',
            'last_login_at' => 'datetime',
            'last_active_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')->withPivot('role', 'status')->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class)->withoutGlobalScopes();
    }

    public function currentTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function coachProfile(): HasOne
    {
        return $this->hasOne(CoachProfile::class);
    }

    public function membershipFor(?string $tenantId = null): ?Membership
    {
        $tenantId ??= app(TenantContext::class)->id();
        if ($tenantId === null) {
            return null;
        }

        return Membership::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $this->id)
            ->where('status', 'active')
            ->first();
    }

    public function roleIn(?string $tenantId = null): ?string
    {
        return $this->membershipFor($tenantId)?->role;
    }

    public function isStaffIn(?string $tenantId = null): bool
    {
        return in_array($this->roleIn($tenantId), ['owner', 'coach', 'assistant'], true);
    }

    public function isOwnerIn(?string $tenantId = null): bool
    {
        return $this->roleIn($tenantId) === 'owner';
    }

    public function isClientIn(?string $tenantId = null): bool
    {
        return $this->roleIn($tenantId) === 'client';
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    public function firstName(): string
    {
        return explode(' ', trim($this->name))[0] ?? $this->name;
    }

    public function preferredLocale(): string
    {
        return $this->locale ?: 'en';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_super_admin;
    }
}
