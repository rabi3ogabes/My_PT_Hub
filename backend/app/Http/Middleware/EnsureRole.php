<?php

namespace App\Http\Middleware;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Usage: ->middleware('role:owner,coach'). Must run after ResolveTenant. */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var User $user */
        $user = $request->user();
        $role = $user->roleIn();
        if (! $user->is_super_admin && ! in_array($role, $roles, true)) {
            throw ApiException::forbidden(__('api.role_required'));
        }

        return $next($request);
    }
}
