<?php

namespace App\Http\Middleware;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Web (session) counterpart of ResolveTenant for the coach dashboard:
 * sets the TenantContext from the user's current tenant (or first active
 * membership), shares $tenant with views, sends clients to the "Get the app"
 * page and platform admins without a workspace to /admin.
 */
class WebTenant
{
    public function __construct(private readonly TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $tenant = self::resolve($user);
        $onAppPage = $request->routeIs('app.get-the-app');

        if (! $tenant) {
            if ($user->is_super_admin) {
                return redirect('/admin');
            }

            return $onAppPage ? $next($request) : redirect()->route('app.get-the-app');
        }

        $this->context->set($tenant);
        View::share('tenant', $tenant);

        $role = $user->roleIn($tenant->id);
        $isStaff = in_array($role, ['owner', 'coach', 'assistant'], true) || $user->is_super_admin;

        if (! $isStaff && ! $onAppPage) {
            return redirect()->route('app.get-the-app');
        }

        if ($isStaff && $role === 'owner' && ! $tenant->onboarded_at && $request->routeIs('app.dashboard')) {
            return redirect()->route('app.onboarding');
        }

        return $next($request);
    }

    /** Current tenant if the user may use it, else the first active membership. */
    public static function resolve(User $user): ?Tenant
    {
        if ($user->current_tenant_id) {
            $tenant = Tenant::query()->find($user->current_tenant_id);
            if ($tenant && ($user->is_super_admin || $user->membershipFor($tenant->id) !== null)) {
                return $tenant;
            }
        }

        $membership = Membership::withoutGlobalScopes()
            ->where('user_id', $user->id)->where('status', 'active')
            ->orderByRaw("CASE role WHEN 'owner' THEN 0 WHEN 'coach' THEN 1 WHEN 'assistant' THEN 2 ELSE 3 END")
            ->first();
        $tenant = $membership ? Tenant::query()->find($membership->tenant_id) : null;

        if ($tenant && $user->current_tenant_id !== $tenant->id) {
            $user->forceFill(['current_tenant_id' => $tenant->id])->saveQuietly();
        }

        return $tenant;
    }
}
