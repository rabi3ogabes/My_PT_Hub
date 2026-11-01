<?php

namespace App\Livewire\App\Concerns;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use App\Http\Middleware\WebTenant;

/**
 * Livewire update requests do not pass through the page's route middleware, so
 * every coach-app component re-establishes the tenant context (and staff-only
 * access) on each request.
 */
trait WithTenant
{
    public function bootWithTenant(): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user !== null, 401);
        $tenant = WebTenant::resolve($user);
        abort_unless($tenant !== null, 403);
        abort_unless($user->is_super_admin || $user->isStaffIn($tenant->id), 403);
        app(TenantContext::class)->set($tenant);
    }

    protected function tenant(): Tenant
    {
        return app(TenantContext::class)->require();
    }

    protected function user(): User
    {
        /** @var User */
        return auth()->user();
    }

    protected function isOwner(): bool
    {
        return $this->user()->is_super_admin || $this->user()->isOwnerIn($this->tenant()->id);
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', type: $type, message: $message);
    }
}
