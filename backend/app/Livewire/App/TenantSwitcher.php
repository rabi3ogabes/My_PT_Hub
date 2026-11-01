<?php

namespace App\Livewire\App;

use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use Livewire\Component;

/** Switch between workspaces the user is staff in. Hidden with a single workspace. */
class TenantSwitcher extends Component
{
    public function switchTo(string $tenantId): mixed
    {
        $user = auth()->user();
        abort_unless($user && ($user->is_super_admin || $user->isStaffIn($tenantId)), 403);
        $user->forceFill(['current_tenant_id' => $tenantId])->save();

        return $this->redirect(route('app.dashboard'));
    }

    public function render()
    {
        $user = auth()->user();
        $ids = $user ? Membership::withoutGlobalScopes()->where('user_id', $user->id)->where('status', 'active')
            ->whereIn('role', ['owner', 'coach', 'assistant'])->pluck('role', 'tenant_id') : collect();
        $tenants = $ids->isEmpty() ? collect() : Tenant::query()->whereIn('id', $ids->keys())->orderBy('name')->get(['id', 'name', 'logo_path', 'brand_color']);

        return view('livewire.app.tenant-switcher', [
            'tenants' => $tenants,
            'roles' => $ids,
            'current' => $user?->current_tenant_id,
        ]);
    }
}
