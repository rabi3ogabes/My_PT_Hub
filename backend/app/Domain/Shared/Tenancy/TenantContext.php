<?php

namespace App\Domain\Shared\Tenancy;

use App\Domain\Tenancy\Models\Tenant;
use Closure;

/**
 * Holds the tenant (coaching business) the current request / job operates in.
 * Tenant-owned models are automatically filtered and stamped with this tenant.
 */
final class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId($tenant?->id);
        }
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?string
    {
        return $this->tenant?->id;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    public function require(): Tenant
    {
        return $this->tenant ?? throw new \RuntimeException('No tenant in context.');
    }

    /**
     * Run a callback inside another tenant, restoring the previous one afterwards.
     *
     * @template T
     * @param  Closure(): T  $callback
     * @return T
     */
    public function run(?Tenant $tenant, Closure $callback): mixed
    {
        $previous = $this->tenant;
        $this->set($tenant);
        try {
            return $callback();
        } finally {
            $this->set($previous);
        }
    }
}
