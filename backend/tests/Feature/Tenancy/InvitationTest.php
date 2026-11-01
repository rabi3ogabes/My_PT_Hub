<?php

use App\Domain\Tenancy\Models\Invitation;
use App\Notifications\InvitationNotification;
use Illuminate\Support\Facades\Notification;

it('lets a coach invite a client who then registers with the code', function () {
    Notification::fake();
    [$tenant, $owner] = $this->createCoachTenant();

    $invite = $this->actingAsIn($owner, $tenant)->postJson('/api/v1/clients', ['name' => 'Layla', 'email' => 'layla@example.com'])
        ->assertCreated()->json('data');
    Notification::assertSentOnDemand(InvitationNotification::class);
    expect($invite['code'])->toHaveLength(10)->and($invite['link'])->toContain('/invite/');

    app('auth')->forgetGuards();
    $this->withHeaders(['X-Tenant' => ''])->getJson("/api/v1/invitations/{$invite['code']}")
        ->assertOk()->assertJsonPath('data.tenant.name', $tenant->name);

    $session = $this->postJson('/api/v1/auth/register', [
        'name' => 'Layla', 'email' => 'layla@example.com', 'password' => 'Secret123',
        'account_type' => 'client', 'invite_code' => $invite['code'], 'terms' => true,
    ])->assertCreated();
    $session->assertJsonPath('data.tenants.0.id', $tenant->id)->assertJsonPath('data.tenants.0.role', 'client');

    expect(Invitation::withoutGlobalScopes()->find($invite['id'])->accepted_at)->not->toBeNull();
    expectApiError($this->getJson("/api/v1/invitations/{$invite['code']}"), 410, 'invitation_invalid');
});

it('enforces the 3-client limit on the free plan', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $tenant->update(['plan' => 'free']);
    foreach (range(1, 3) as $i) {
        $this->addClient($tenant, $owner);
    }

    expectApiError($this->actingAsIn($owner, $tenant)->postJson('/api/v1/clients', ['name' => 'Fourth', 'email' => 'four@example.com']), 402, 'plan_limit_reached');
});

it('imports clients from CSV and skips duplicates', function () {
    Notification::fake();
    [$tenant, $owner] = $this->createCoachTenant();
    $csv = "name,email,phone\nAli,ali@example.com,\nMona,mona@example.com,\n,,\nBad,not-an-email,\n";
    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('clients.csv', $csv);

    $this->actingAsIn($owner, $tenant)->postJson('/api/v1/clients/import', ['file' => $file])
        ->assertOk()->assertJsonPath('data.created', 2)->assertJsonCount(1, 'data.errors');
    $file2 = \Illuminate\Http\UploadedFile::fake()->createWithContent('clients.csv', $csv);
    $this->postJson('/api/v1/clients/import', ['file' => $file2])->assertOk()->assertJsonPath('data.skipped', 2);
});

it('archives a client and removes their access', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $client = $this->addClient($tenant, $owner);

    $this->actingAsIn($owner, $tenant)->deleteJson("/api/v1/clients/{$client->id}")->assertOk();
    app('auth')->forgetGuards();
    expectApiError($this->actingAsIn($client, $tenant)->getJson('/api/v1/tenant'), 403, 'forbidden');
});

it('updates a client profile with encrypted health fields', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $client = $this->addClient($tenant, $owner);

    $this->actingAsIn($owner, $tenant)->patchJson("/api/v1/clients/{$client->id}", [
        'goal' => 'fat_loss', 'injuries' => 'Left knee ACL repair 2023', 'date_of_birth' => '1992-05-10', 'tags' => ['vip'],
    ])->assertOk()->assertJsonPath('data.injuries', 'Left knee ACL repair 2023')->assertJsonPath('data.goal', 'fat_loss');

    $raw = \Illuminate\Support\Facades\DB::table('client_profiles')->where('user_id', $client->id)->value('injuries');
    expect($raw)->not->toContain('ACL');
});
