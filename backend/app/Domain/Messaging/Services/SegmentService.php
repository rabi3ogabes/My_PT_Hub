<?php

namespace App\Domain\Messaging\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\ProgramAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Resolves a broadcast segment {client_ids, tags, group_ids, program_id, at_risk}
 * to the active clients of a tenant. All provided filters are combined (AND);
 * tags and group_ids match any of the listed values. An empty segment means
 * every active client.
 */
final class SegmentService
{
    /**
     * @param  array{client_ids?: list<string>, tags?: list<string>, group_ids?: list<string>, program_id?: string|null, at_risk?: bool|null}  $segment
     * @return Collection<int, User>
     */
    public function resolve(Tenant $tenant, array $segment): Collection
    {
        $ids = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'client')->where('status', 'active')->pluck('user_id')->all();

        if (! empty($segment['client_ids'])) {
            $ids = array_values(array_intersect($ids, $segment['client_ids']));
        }
        if (! empty($segment['group_ids'])) {
            $groupMembers = DB::table('client_group_members')
                ->join('client_groups', 'client_groups.id', '=', 'client_group_members.client_group_id')
                ->where('client_groups.tenant_id', $tenant->id)
                ->whereIn('client_group_members.client_group_id', $segment['group_ids'])
                ->pluck('client_group_members.user_id')->all();
            $ids = array_values(array_intersect($ids, $groupMembers));
        }
        if (! empty($segment['program_id'])) {
            $assigned = ProgramAssignment::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('program_id', $segment['program_id'])
                ->where('status', 'active')->pluck('client_id')->all();
            $ids = array_values(array_intersect($ids, $assigned));
        }
        if (! empty($segment['at_risk']) || ! empty($segment['tags'])) {
            $profiles = ClientProfile::withoutGlobalScopes()->where('tenant_id', $tenant->id)->whereIn('user_id', $ids)->get(['user_id', 'risk_status', 'tags']);
            if (! empty($segment['at_risk'])) {
                $profiles = $profiles->filter(fn (ClientProfile $p) => in_array($p->risk_status, ['amber', 'red'], true));
            }
            if (! empty($segment['tags'])) {
                $wanted = array_map('mb_strtolower', $segment['tags']);
                $profiles = $profiles->filter(fn (ClientProfile $p) => array_intersect(array_map('mb_strtolower', (array) ($p->tags ?? [])), $wanted) !== []);
            }
            $ids = $profiles->pluck('user_id')->all();
        }

        return User::query()->whereIn('id', $ids)->orderBy('name')->get();
    }
}
