<?php

namespace App\Http\Api\V1\Calendar\Concerns;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Membership;

/** Resolves user ids from input to active members of the current tenant (404 otherwise). */
trait ResolvesMembers
{
    protected function staffMember(string $userId): User
    {
        return $this->member($userId, ['owner', 'coach', 'assistant']);
    }

    protected function clientMember(string $userId): User
    {
        return $this->member($userId, ['client']);
    }

    /** @param  list<string>  $roles */
    private function member(string $userId, array $roles): User
    {
        $exists = Membership::query()->where('user_id', $userId)->where('status', 'active')->whereIn('role', $roles)->exists();
        $user = $exists ? User::query()->find($userId) : null;
        if (! $user) {
            throw new ApiException('not_found', __('calendar.errors.member_not_found'), 404);
        }

        return $user;
    }
}
