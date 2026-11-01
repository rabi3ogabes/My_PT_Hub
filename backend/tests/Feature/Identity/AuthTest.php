<?php

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Tenant;

it('registers a coach with a business workspace on a Pro trial', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Sara Al-Thani',
        'email' => 'sara@example.com',
        'password' => 'Secret123',
        'account_type' => 'coach',
        'business_name' => 'Sara Strength',
        'currency' => 'QAR',
        'terms' => true,
        'platform' => 'ios',
        'device_name' => 'iPhone',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['access_token', 'refresh_token', 'expires_at', 'user' => ['id', 'email'], 'tenants']])
        ->assertJsonPath('data.tenants.0.name', 'Sara Strength')
        ->assertJsonPath('data.tenants.0.role', 'owner')
        ->assertJsonPath('data.tenants.0.plan', 'pro');

    $tenant = Tenant::where('name', 'Sara Strength')->first();
    expect($tenant->currency)->toBe('QAR')
        ->and($tenant->trial_ends_at)->not->toBeNull()
        ->and($tenant->subscription->status)->toBe('trialing');
});

it('registers a standalone client in a personal workspace', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Omar', 'email' => 'omar@example.com', 'password' => 'Secret123',
        'account_type' => 'standalone', 'terms' => true,
    ])->assertCreated()->assertJsonPath('data.tenants.0.type', 'personal')->assertJsonPath('data.tenants.0.role', 'client');
});

it('rejects client registration with an invalid invite code', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'X', 'email' => 'x@example.com', 'password' => 'Secret123',
        'account_type' => 'client', 'invite_code' => 'NOPE', 'terms' => true,
    ]);
    expectApiError($response, 410, 'invitation_invalid');
    expect(User::where('email', 'x@example.com')->exists())->toBeFalse();
});

it('locks the account after repeated failed logins', function () {
    $user = User::factory()->create(['email' => 'lock@example.com', 'password' => 'Secret123']);
    foreach (range(1, 5) as $i) {
        $this->postJson('/api/v1/auth/login', ['email' => 'lock@example.com', 'password' => 'wrong'.$i]);
    }
    expect($user->fresh()->locked_until)->not->toBeNull();
    expectApiError($this->postJson('/api/v1/auth/login', ['email' => 'lock@example.com', 'password' => 'Secret123']), 423, 'account_locked');
});

it('rotates refresh tokens and rejects reuse', function () {
    User::factory()->create(['email' => 'r@example.com', 'password' => 'Secret123']);
    $login = $this->postJson('/api/v1/auth/login', ['email' => 'r@example.com', 'password' => 'Secret123'])->assertOk();
    $refresh = $login->json('data.refresh_token');

    $new = $this->postJson('/api/v1/auth/refresh', ['refresh_token' => $refresh])->assertOk();
    expect($new->json('data.refresh_token'))->not->toBe($refresh);

    expectApiError($this->postJson('/api/v1/auth/refresh', ['refresh_token' => $refresh]), 401, 'invalid_refresh_token');
});

it('does not accept a refresh token as an access token', function () {
    User::factory()->create(['email' => 'a@example.com', 'password' => 'Secret123']);
    $login = $this->postJson('/api/v1/auth/login', ['email' => 'a@example.com', 'password' => 'Secret123']);
    $this->withToken($login->json('data.refresh_token'))->getJson('/api/v1/me')->assertForbidden();
    app('auth')->forgetGuards();
    $this->withToken($login->json('data.access_token'))->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.user.email', 'a@example.com');
});

it('requires a TOTP code when two-factor is enabled', function () {
    $user = User::factory()->create(['email' => 't@example.com', 'password' => 'Secret123']);
    $svc = app(\App\Domain\Identity\Services\AuthService::class);
    $secret = $svc->beginTwoFactor($user)['secret'];
    $svc->confirmTwoFactor($user->fresh(), (new \PragmaRX\Google2FA\Google2FA)->getCurrentOtp($secret));

    $login = $this->postJson('/api/v1/auth/login', ['email' => 't@example.com', 'password' => 'Secret123'])->assertOk()->assertJsonPath('data.two_factor_required', true);
    $this->postJson('/api/v1/auth/two-factor', ['challenge' => $login->json('data.challenge'), 'code' => (new \PragmaRX\Google2FA\Google2FA)->getCurrentOtp($secret)])
        ->assertOk()->assertJsonStructure(['data' => ['access_token']]);
});
