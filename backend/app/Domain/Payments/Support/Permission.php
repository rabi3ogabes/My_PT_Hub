<?php

namespace App\Domain\Payments\Support;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;

/** Granular permission checks for staff endpoints (owner has all; coaches/assistants per Roles::map). */
final class Permission
{
    public static function assert(User $user, string ...$permissions): void
    {
        if ($user->is_super_admin) {
            return;
        }
        $user->unsetRelation('roles')->unsetRelation('permissions');
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return;
            }
        }
        throw ApiException::forbidden(__('api.forbidden'));
    }
}
