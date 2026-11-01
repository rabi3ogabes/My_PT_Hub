<?php

namespace App\Http\Api\V1\Support;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Membership;

/** Resolves client users that belong (active, role=client) to the current tenant. */
final class TenantClients
{
    public static function assert(User $client): User
    {
        $tenantId = app(TenantContext::class)->id();
        $isClient = Membership::withoutGlobalScopes()->where('tenant_id', $tenantId)->where('user_id', $client->id)
            ->where('role', 'client')->where('status', 'active')->exists();
        if (! $isClient) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }

        return $client;
    }

    /**
     * @param  list<string>  $ids
     * @return list<User>
     */
    public static function find(array $ids, string $field = 'client_ids'): array
    {
        $ids = array_values(array_unique($ids));
        $tenantId = app(TenantContext::class)->id();
        $valid = Membership::withoutGlobalScopes()->where('tenant_id', $tenantId)->whereIn('user_id', $ids)
            ->where('role', 'client')->where('status', 'active')->pluck('user_id')->all();
        if (count($valid) !== count($ids)) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), [$field => [__('nutrition.errors.client_not_found')]]);
        }

        return User::query()->whereIn('id', $ids)->get()->all();
    }

    /** @return list<string> */
    public static function allIds(): array
    {
        return Membership::withoutGlobalScopes()->where('tenant_id', app(TenantContext::class)->id())
            ->where('role', 'client')->where('status', 'active')->pluck('user_id')->all();
    }
}
