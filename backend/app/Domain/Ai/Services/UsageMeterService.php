<?php

namespace App\Domain\Ai\Services;

use App\Domain\Billing\Models\UsageMeter;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\DB;

/** Monthly per-tenant AI counters in usage_meters (ai_requests, ai_tokens). */
final class UsageMeterService
{
    public function period(): string
    {
        return now()->utc()->format('Y-m');
    }

    public function record(Tenant $tenant, int $tokens, int $costMicros): void
    {
        $this->increment($tenant, 'ai_requests', 1, $costMicros);
        $this->increment($tenant, 'ai_tokens', $tokens, $costMicros);
    }

    public function requestsThisMonth(Tenant $tenant): int
    {
        return (int) $this->meter($tenant, 'ai_requests')?->value;
    }

    public function costThisMonth(Tenant $tenant): int
    {
        return (int) $this->meter($tenant, 'ai_requests')?->cost_micros;
    }

    public function meter(Tenant $tenant, string $metric, ?string $period = null): ?UsageMeter
    {
        return UsageMeter::query()->where('tenant_id', $tenant->id)->where('metric', $metric)->where('period', $period ?? $this->period())->first();
    }

    private function increment(Tenant $tenant, string $metric, int $value, int $costMicros): void
    {
        DB::transaction(function () use ($tenant, $metric, $value, $costMicros) {
            $meter = UsageMeter::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'metric' => $metric, 'period' => $this->period()],
                ['value' => 0, 'cost_micros' => 0],
            );
            UsageMeter::query()->whereKey($meter->id)->update([
                'value' => DB::raw('value + '.max(0, $value)),
                'cost_micros' => DB::raw('cost_micros + '.max(0, $costMicros)),
                'updated_at' => now(),
            ]);
        });
    }
}
