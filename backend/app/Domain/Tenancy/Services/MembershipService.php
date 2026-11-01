<?php

namespace App\Domain\Tenancy\Services;

use App\Domain\Billing\Services\PlanGate;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Adds users to tenants and keeps the pivot role and the spatie team role in sync. */
final class MembershipService
{
    public function __construct(private readonly PlanGate $gate, private readonly TenantContext $context) {}

    /**
     * @param  array<string, mixed>  $clientProfile
     */
    public function add(Tenant $tenant, User $user, string $role, ?string $coachId = null, array $clientProfile = [], bool $enforceLimits = true): Membership
    {
        if (! in_array($role, User::ROLES, true)) {
            throw new InvalidArgumentException("Unknown role [$role]");
        }

        return DB::transaction(function () use ($tenant, $user, $role, $coachId, $clientProfile, $enforceLimits) {
            $existing = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('user_id', $user->id)->first();

            if ($enforceLimits && (! $existing || $existing->status !== 'active')) {
                $role === 'client' ? $this->gate->assertCanAddClient($tenant) : ($role !== 'owner' ? $this->gate->assertCanAddStaff($tenant) : null);
            }

            $membership = $existing ?? new Membership(['tenant_id' => $tenant->id, 'user_id' => $user->id]);
            $membership->fill(['role' => $role, 'status' => 'active', 'joined_at' => $membership->joined_at ?? now()])->save();

            $this->context->run($tenant, function () use ($user, $role, $coachId, $clientProfile, $tenant) {
                $user->syncRoles([$role]);
                if ($role === 'client') {
                    ClientProfile::withTrashed()->updateOrCreate(
                        ['tenant_id' => $tenant->id, 'user_id' => $user->id],
                        array_merge(['coach_id' => $coachId, 'status' => 'active', 'deleted_at' => null], $clientProfile),
                    );
                }
            });

            if (! $user->current_tenant_id) {
                $user->forceFill(['current_tenant_id' => $tenant->id])->save();
            }

            ActivityLogger::log('membership.added', $membership, ['role' => $role], tenantId: $tenant->id);

            return $membership;
        });
    }

    public function changeRole(Tenant $tenant, User $user, string $role): Membership
    {
        $membership = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('user_id', $user->id)->firstOrFail();
        if ($membership->role === 'owner' && $role !== 'owner' && $tenant->memberships()->where('role', 'owner')->count() <= 1) {
            throw new \App\Domain\Shared\Support\ApiException('last_owner', __('api.last_owner'), 422);
        }
        $membership->update(['role' => $role]);
        $this->context->run($tenant, fn () => $user->syncRoles([$role]));

        return $membership;
    }

    public function archive(Tenant $tenant, User $user): void
    {
        $membership = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('user_id', $user->id)->firstOrFail();
        $membership->update(['status' => 'archived']);
        $this->context->run($tenant, function () use ($user, $tenant) {
            ClientProfile::query()->where('tenant_id', $tenant->id)->where('user_id', $user->id)->update(['status' => 'archived']);
        });
        ActivityLogger::log('membership.archived', $membership, tenantId: $tenant->id);
    }
}
