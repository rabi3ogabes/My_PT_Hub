<?php

namespace App\Http\Middleware;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active tenant from (in order) the X-Tenant header, the
 * {slug}.app.domain subdomain, or the user's current tenant — and verifies
 * the authenticated user is an active member of it.
 */
class ResolveTenant
{
    public function __construct(private readonly TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            throw new \Illuminate\Auth\AuthenticationException;
        }

        $tenant = $this->fromHeader($request) ?? $this->fromSubdomain($request) ?? $user->currentTenant;

        if (! $tenant) {
            $first = $user->memberships()->where('status', 'active')->first();
            $tenant = $first ? Tenant::find($first->tenant_id) : null;
        }

        if (! $tenant) {
            throw new ApiException('no_tenant', __('api.no_tenant'), 409);
        }

        if (! $user->is_super_admin && $user->membershipFor($tenant->id) === null) {
            throw ApiException::forbidden(__('api.not_a_member'));
        }

        if (! $user->is_super_admin && $tenant->setting('suspended_at')) {
            throw new ApiException('tenant_suspended', __('api.tenant_suspended'), 423);
        }

        if ($user->current_tenant_id !== $tenant->id) {
            $user->forceFill(['current_tenant_id' => $tenant->id])->saveQuietly();
        }

        $this->context->set($tenant);

        return $next($request);
    }

    private function fromHeader(Request $request): ?Tenant
    {
        $value = $request->header('X-Tenant');
        if (! $value) {
            return null;
        }

        return Tenant::query()->where('id', $value)->orWhere('slug', $value)->first()
            ?? throw new ApiException('tenant_not_found', __('api.tenant_not_found'), 404);
    }

    private function fromSubdomain(Request $request): ?Tenant
    {
        $base = config('app.tenant_domain');
        $host = $request->getHost();
        if (! $base || ! str_ends_with($host, '.'.$base)) {
            return null;
        }
        $slug = substr($host, 0, -strlen('.'.$base));

        return $slug !== '' && $slug !== 'www' ? Tenant::query()->where('slug', $slug)->first() : null;
    }
}
