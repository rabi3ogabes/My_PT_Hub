<?php

use App\Domain\Identity\Models\User;
use Database\Seeders\Modules\SuperAdminSeeder;
use Illuminate\Support\Facades\Hash;

afterEach(function () {
    foreach (['SUPER_ADMIN_EMAIL', 'SUPER_ADMIN_PASSWORD'] as $key) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }
});

function setAdminEnv(?string $email, ?string $password): void
{
    foreach (['SUPER_ADMIN_EMAIL' => $email, 'SUPER_ADMIN_PASSWORD' => $password] as $key => $value) {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        } else {
            putenv("{$key}={$value}");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
    }
}

it('does nothing unless both env variables are set', function () {
    setAdminEnv('boss@aurum.test', null);
    $this->seed(SuperAdminSeeder::class);

    expect(User::query()->where('is_super_admin', true)->exists())->toBeFalse();
});

it('creates the super admin from the environment', function () {
    setAdminEnv('Boss@Aurum.test', 'a-very-long-secret-1');
    $this->seed(SuperAdminSeeder::class);

    $admin = User::query()->where('email', 'boss@aurum.test')->sole();
    expect($admin->is_super_admin)->toBeTrue()
        ->and(Hash::check('a-very-long-secret-1', $admin->password))->toBeTrue();
});

it('promotes an existing account without changing its password', function () {
    $user = User::factory()->create(['email' => 'boss@aurum.test']);
    setAdminEnv('boss@aurum.test', 'another-long-secret');
    $this->seed(SuperAdminSeeder::class);

    expect($user->refresh()->is_super_admin)->toBeTrue()
        ->and(Hash::check('another-long-secret', $user->password))->toBeFalse();
});
