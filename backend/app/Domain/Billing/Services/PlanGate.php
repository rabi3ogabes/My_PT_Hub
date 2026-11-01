<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\FeatureFlag;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Cache;

/** Plan feature gates and limits ("Free = 3 clients", "AI copilot in Pro"). */
final class PlanGate
{
    public function plan(Tenant $tenant): SaasPlan
    {
        $code = $tenant->plan ?: 'free';

        return Cache::remember("plan:{$code}", 300, fn () => SaasPlan::query()->where('code', $code)->first()
            ?? SaasPlan::query()->where('code', 'free')->first()
            ?? new SaasPlan(['code' => 'free', 'limits' => ['clients' => 3, 'coaches' => 1], 'features' => []]));
    }

    /**
     * Resolution order:
     *  1. A disabled flag is a kill switch (false for everyone).
     *  2. Tenant allow-list on the flag wins (beta access).
     *  3. Flag restricted to plans / partial rollout narrows access.
     *  4. Otherwise the plan's feature list decides; a flag for a feature that
     *     no plan lists (pure beta flag) grants it when enabled.
     */
    public function feature(Tenant $tenant, string $feature): bool
    {
        $flag = Cache::remember("flag:{$feature}", 60, fn () => FeatureFlag::query()->where('key', $feature)->first());

        if ($flag) {
            if (! $flag->enabled) {
                return false;
            }
            if (in_array($tenant->id, $flag->tenants ?? [], true)) {
                return true;
            }
            if (! empty($flag->plans) && ! in_array($tenant->plan, $flag->plans, true)) {
                return false;
            }
            if ($flag->rollout < 100 && (crc32($feature.$tenant->id) % 100) >= $flag->rollout) {
                return false;
            }
        }

        if ($this->plan($tenant)->hasFeature($feature)) {
            return true;
        }

        return $flag !== null && ! $this->anyPlanHas($feature);
    }

    private function anyPlanHas(string $feature): bool
    {
        return Cache::remember("plan-feature:{$feature}", 300, fn () => SaasPlan::query()->get()->contains(fn (SaasPlan $p) => $p->hasFeature($feature)));
    }

    public function limit(Tenant $tenant, string $key): ?int
    {
        return $this->plan($tenant)->limit($key);
    }

    public function canAddClient(Tenant $tenant): bool
    {
        $limit = $this->limit($tenant, 'clients');

        return $limit === null || $tenant->clientCount() < $limit;
    }

    public function assertCanAddClient(Tenant $tenant): void
    {
        if (! $this->canAddClient($tenant)) {
            throw ApiException::limit(__('billing.client_limit', ['limit' => $this->limit($tenant, 'clients')]));
        }
    }

    public function assertCanAddStaff(Tenant $tenant): void
    {
        $limit = $this->limit($tenant, 'coaches');
        if ($limit !== null && $tenant->staffCount() >= $limit) {
            throw ApiException::limit(__('billing.coach_limit', ['limit' => $limit]));
        }
    }

    public function assertFeature(Tenant $tenant, string $feature): void
    {
        if (! $this->feature($tenant, $feature)) {
            throw ApiException::limit(__('billing.feature_locked'));
        }
    }
}
