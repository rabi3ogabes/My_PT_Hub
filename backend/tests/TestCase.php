<?php

namespace Tests;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\MembershipService;
use App\Domain\Tenancy\Services\TenantProvisioner;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    protected bool $seedPlatform = true;

    protected function setUp(): void
    {
        parent::setUp();
        if ($this->seedPlatform && in_array(\Illuminate\Foundation\Testing\RefreshDatabase::class, class_uses_recursive(static::class), true)) {
            $this->seed([RoleSeeder::class, PlanSeeder::class]);
        }
    }

    /**
     * Creates a coaching business with its owner. Returns [tenant, owner].
     *
     * @return array{0: Tenant, 1: User}
     */
    protected function createCoachTenant(array $userAttributes = [], array $tenantAttributes = []): array
    {
        $owner = User::factory()->create($userAttributes);
        $tenant = app(TenantProvisioner::class)->provision($owner, $tenantAttributes + ['name' => 'Test Coaching '.$owner->id]);
        app(TenantContext::class)->set(null);

        return [$tenant->refresh(), $owner->refresh()];
    }

    protected function addClient(Tenant $tenant, ?User $coach = null, array $userAttributes = []): User
    {
        $client = User::factory()->create($userAttributes);
        app(MembershipService::class)->add($tenant, $client, 'client', $coach?->id, enforceLimits: false);
        app(TenantContext::class)->set(null);

        return $client->refresh();
    }

    protected function addStaff(Tenant $tenant, string $role = 'coach'): User
    {
        $user = User::factory()->create();
        app(MembershipService::class)->add($tenant, $user, $role, enforceLimits: false);
        app(TenantContext::class)->set(null);

        return $user->refresh();
    }

    /** Authenticates as the user for API calls, scoped to the tenant via X-Tenant. */
    protected function actingAsIn(User $user, ?Tenant $tenant = null): static
    {
        Sanctum::actingAs($user, ['access']);
        app(TenantContext::class)->set(null);
        if ($tenant) {
            $this->withHeader('X-Tenant', $tenant->id);
        }

        return $this;
    }

    /** Runs the callback inside a tenant context (for arranging data). */
    protected function inTenant(Tenant $tenant, \Closure $callback): mixed
    {
        return app(TenantContext::class)->run($tenant, $callback);
    }
}
