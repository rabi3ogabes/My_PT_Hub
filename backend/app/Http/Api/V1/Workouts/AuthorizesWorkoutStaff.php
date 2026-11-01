<?php

namespace App\Http\Api\V1\Workouts;

use App\Domain\Shared\Support\ApiException;

/** Owners and coaches manage the library; assistants need the explicit permission. */
trait AuthorizesWorkoutStaff
{
    protected function authorizeManage(string $permission): void
    {
        $user = $this->user();
        if ($user->is_super_admin || in_array($user->roleIn(), ['owner', 'coach'], true)) {
            return;
        }
        if (! $user->can($permission)) {
            throw ApiException::forbidden();
        }
    }
}
