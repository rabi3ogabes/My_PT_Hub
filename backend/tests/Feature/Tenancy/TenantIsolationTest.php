<?php

use App\Domain\Tenancy\Models\ClientGroup;
use App\Domain\Tenancy\Models\ClientProfile;

it('never exposes another tenant\'s clients through listing or direct access', function () {
    [$tenantA, $ownerA] = $this->createCoachTenant();
    [$tenantB, $ownerB] = $this->createCoachTenant();
    $clientA = $this->addClient($tenantA, $ownerA);
    $clientB = $this->addClient($tenantB, $ownerB);

    $this->actingAsIn($ownerA, $tenantA);
    $ids = collect($this->getJson('/api/v1/clients')->assertOk()->json('data'))->pluck('id');
    expect($ids)->toContain($clientA->id)->not->toContain($clientB->id);

    expectApiError($this->getJson("/api/v1/clients/{$clientB->id}"), 404, 'not_found');
});

it('blocks route-model binding across tenants', function () {
    [$tenantA, $ownerA] = $this->createCoachTenant();
    [$tenantB, $ownerB] = $this->createCoachTenant();
    $groupB = $this->inTenant($tenantB, fn () => ClientGroup::create(['name' => 'VIP B']));

    $this->actingAsIn($ownerA, $tenantA)->getJson("/api/v1/groups/{$groupB->id}")->assertNotFound();
});

it('rejects an X-Tenant header for a workspace the user does not belong to', function () {
    [$tenantA, $ownerA] = $this->createCoachTenant();
    [$tenantB] = $this->createCoachTenant();

    expectApiError($this->actingAsIn($ownerA, $tenantB)->getJson('/api/v1/tenant'), 403, 'forbidden');
});

it('keeps clients out of staff endpoints', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $client = $this->addClient($tenant, $owner);

    expectApiError($this->actingAsIn($client, $tenant)->getJson('/api/v1/clients'), 403, 'forbidden');
});

it('scopes model queries to the active tenant context', function () {
    [$tenantA, $ownerA] = $this->createCoachTenant();
    [$tenantB, $ownerB] = $this->createCoachTenant();
    $this->addClient($tenantA, $ownerA);
    $this->addClient($tenantB, $ownerB);

    expect($this->inTenant($tenantA, fn () => ClientProfile::count()))->toBe(1)
        ->and($this->inTenant($tenantB, fn () => ClientProfile::count()))->toBe(1);
});
