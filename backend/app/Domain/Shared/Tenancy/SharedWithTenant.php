<?php

namespace App\Domain\Shared\Tenancy;

use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * For library content that is either global (tenant_id null, curated by the
 * platform) or private to one tenant. New rows created inside a tenant are
 * private to it unless tenant_id is explicitly set to null by the platform.
 */
trait SharedWithTenant
{
    public static function bootSharedWithTenant(): void
    {
        static::addGlobalScope(new SharedLibraryScope);

        static::creating(function (Model $model): void {
            if (! array_key_exists('tenant_id', $model->getAttributes())) {
                $model->setAttribute('tenant_id', app(TenantContext::class)->id());
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isGlobal(): bool
    {
        return $this->getAttribute('tenant_id') === null;
    }

    public function isOwnedByCurrentTenant(): bool
    {
        $id = app(TenantContext::class)->id();

        return $id !== null && $this->getAttribute('tenant_id') === $id;
    }
}
