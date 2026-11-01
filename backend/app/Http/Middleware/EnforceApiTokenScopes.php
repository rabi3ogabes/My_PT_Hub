<?php

namespace App\Http\Middleware;

use App\Domain\Shared\Support\ApiException;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies to personal access tokens only (ability "pat"): pins the request
 * to the token's tenant and checks the route's scope (e.g. clients:write).
 * App session tokens are unaffected.
 */
class EnforceApiTokenScopes
{
    /** First path segment (after /api/v1, and after /me) → scope resource. */
    public const RESOURCES = [
        'clients' => 'clients', 'invitations' => 'clients', 'groups' => 'clients',
        'programs' => 'programs', 'workouts' => 'programs', 'exercises' => 'programs', 'program-assignments' => 'programs',
        'conversations' => 'messages', 'messages' => 'messages',
        'leads' => 'leads',
        'checkins' => 'checkins', 'checkin-forms' => 'checkins',
        'orders' => 'payments', 'subscriptions' => 'payments', 'invoices' => 'payments', 'products' => 'payments', 'analytics' => 'payments',
        'events' => 'calendar', 'bookings' => 'calendar', 'availability' => 'calendar', 'slots' => 'calendar', 'classes' => 'calendar',
        'webhook-endpoints' => 'webhooks',
        'health' => 'health',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ? auth('sanctum')->user()?->currentAccessToken() : null;
        if (! $token instanceof PersonalAccessToken || ! in_array('pat', $token->abilities ?? [], true)) {
            return $next($request);
        }

        $abilities = $token->abilities ?? [];
        $tenantAbility = collect($abilities)->first(fn (string $a) => str_starts_with($a, 'tenant:'));
        if ($tenantAbility) {
            $tenantId = substr($tenantAbility, 7);
            $header = $request->header('X-Tenant');
            if ($header && $header !== $tenantId) {
                throw ApiException::forbidden(__('integrations.errors.token_tenant'));
            }
            $request->headers->set('X-Tenant', $tenantId);
        }

        $required = $this->requiredScope($request);
        if ($required !== null && ! in_array('*', $abilities, true) && ! in_array($required, $abilities, true)) {
            throw ApiException::forbidden(__('integrations.errors.token_scope', ['scope' => $required]));
        }

        return $next($request);
    }

    /** Null means any personal token may call the route. */
    public function requiredScope(Request $request): ?string
    {
        $path = preg_replace('#^api/v1/?#', '', trim($request->path(), '/'));
        $segments = array_values(array_filter(explode('/', (string) $path)));
        $read = in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);

        if ($segments === [] || ($read && in_array($segments[0], ['me', 'tenant'], true) && count($segments) === 1)) {
            return null;
        }
        if ($segments[0] === 'api-tokens') {
            return 'api-tokens:manage'; // never grantable: tokens cannot mint tokens
        }
        $first = $segments[0] === 'me' ? ($segments[1] ?? 'me') : $segments[0];
        $resource = self::RESOURCES[$first] ?? null;
        if ($resource === null) {
            return '*';
        }
        if ($resource === 'webhooks') {
            return 'webhooks:manage';
        }

        return $resource.($read ? ':read' : ':write');
    }
}
