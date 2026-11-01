<?php

namespace Database\Seeders\Modules;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates (or promotes) the platform super admin from the environment only:
 * SUPER_ADMIN_EMAIL + SUPER_ADMIN_PASSWORD (optional SUPER_ADMIN_NAME).
 * Nothing happens unless both are set; no credentials live in the code.
 * An existing account is promoted but its password is never overwritten.
 */
class SuperAdminSeeder extends Seeder
{
    public const MIN_PASSWORD_LENGTH = 12;

    public function run(): void
    {
        $email = trim((string) env('SUPER_ADMIN_EMAIL', ''));
        $password = (string) env('SUPER_ADMIN_PASSWORD', '');

        if ($email === '' || $password === '') {
            $this->command?->info('SuperAdminSeeder skipped: SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD are not both set.');

            return;
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->command?->error('SuperAdminSeeder skipped: SUPER_ADMIN_EMAIL is not a valid email address.');

            return;
        }

        $user = User::withTrashed()->where('email', strtolower($email))->first();

        if ($user === null) {
            if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
                $this->command?->error('SuperAdminSeeder skipped: SUPER_ADMIN_PASSWORD must be at least '.self::MIN_PASSWORD_LENGTH.' characters.');

                return;
            }
            $user = new User([
                'name' => (string) env('SUPER_ADMIN_NAME', 'Platform Admin'),
                'email' => strtolower($email),
                'password' => $password,
            ]);
        } elseif ($user->trashed()) {
            $user->restore();
        }

        $user->forceFill([
            'is_super_admin' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->command?->info("Super admin ready: {$user->email}");
    }
}
