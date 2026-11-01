<?php

namespace App\Filament\Support;

use App\Domain\Shared\Tenancy\SharedLibraryScope;
use App\Domain\Shared\Tenancy\TenantScope;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

/**
 * Base for every super-admin resource.
 *
 * - The panel is only reachable by super admins (User::canAccessPanel), so
 *   tenant-facing policies are bypassed for them explicitly.
 * - Admins operate across all tenants: the tenant and shared-library global
 *   scopes are removed (soft-delete scopes are kept so TrashedFilter works).
 */
abstract class AdminResource extends Resource
{
    public static function shouldSkipAuthorization(): bool
    {
        return auth()->user()?->is_super_admin === true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([TenantScope::class, SharedLibraryScope::class]);
    }
}
