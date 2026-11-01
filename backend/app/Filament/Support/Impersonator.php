<?php

namespace App\Filament\Support;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

/**
 * Super-admin impersonation with an audit trail. The admin's id is kept in
 * session('impersonator_id'); the app layout shows a banner while it is set.
 */
final class Impersonator
{
    public const SESSION_KEY = 'impersonator_id';

    private const GUARD = 'web';

    public function canImpersonate(?User $admin, User $target): bool
    {
        return $admin !== null
            && $admin->is_super_admin
            && ! $target->is_super_admin
            && ! $target->trashed()
            && $admin->getKey() !== $target->getKey()
            && ! $this->isImpersonating();
    }

    /** @throws AuthorizationException */
    public function start(User $admin, User $target): void
    {
        if (! $this->canImpersonate($admin, $target)) {
            ActivityLogger::log('admin.impersonate.denied', $target, ['target_email' => $target->email], causerId: $admin->getKey(), tenantId: $target->current_tenant_id);

            throw new AuthorizationException(__('Super admins cannot be impersonated.'));
        }

        ActivityLogger::log('admin.impersonate.start', $target, [
            'admin_id' => $admin->getKey(),
            'admin_email' => $admin->email,
            'target_email' => $target->email,
        ], causerId: $admin->getKey(), tenantId: $target->current_tenant_id);

        $session = session()->driver();
        Auth::guard(self::GUARD)->login($target);
        $session->regenerate();
        $session->put(self::SESSION_KEY, $admin->getKey());
        // Laravel/Filament AuthenticateSession pin the password hash of the
        // logged-in user; drop it so the new identity is re-pinned.
        $session->forget('password_hash_'.self::GUARD);
    }

    public function isImpersonating(): bool
    {
        return session()->has(self::SESSION_KEY);
    }

    /** Restores the admin. Returns the admin, or null when nothing was impersonated. */
    public function stop(): ?User
    {
        if (! $this->isImpersonating()) {
            return null;
        }

        $session = session()->driver();
        $admin = User::query()->find($session->get(self::SESSION_KEY));
        $target = Auth::guard(self::GUARD)->user();

        ActivityLogger::log('admin.impersonate.stop', $target instanceof User ? $target : null, [
            'admin_id' => $admin?->getKey(),
            'admin_email' => $admin?->email,
            'target_email' => $target instanceof User ? $target->email : null,
        ], causerId: $admin?->getKey(), tenantId: $target instanceof User ? $target->current_tenant_id : null);

        $session->forget([self::SESSION_KEY, 'password_hash_'.self::GUARD]);

        if (! $admin || ! $admin->is_super_admin) {
            Auth::guard(self::GUARD)->logout();
            $session->invalidate();
            $session->regenerateToken();

            return null;
        }

        Auth::guard(self::GUARD)->login($admin);
        $session->regenerate();

        return $admin;
    }
}
