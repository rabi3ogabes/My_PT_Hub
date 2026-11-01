<?php

namespace App\Filament\Support;

use App\Domain\Ai\Models\AiRequest;
use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\Money;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Read-only aggregates for the admin dashboard. */
final class PlatformMetrics
{
    /**
     * Monthly recurring revenue per currency from active subscriptions
     * (annual prices normalised to /12), in minor units.
     *
     * @return array<string, int>
     */
    public static function mrrByCurrency(): array
    {
        return SaasSubscription::query()
            ->where('status', 'active')
            ->get(['price_minor', 'currency', 'interval'])
            ->groupBy('currency')
            ->map(fn ($subs): int => (int) round($subs->sum(fn (SaasSubscription $s): float => $s->interval === 'year' ? $s->price_minor / 12 : $s->price_minor)))
            ->sortDesc()
            ->all();
    }

    public static function formattedMrr(): string
    {
        $mrr = self::mrrByCurrency();
        if ($mrr === []) {
            return Money::format(0, 'USD');
        }

        return collect($mrr)->map(fn (int $minor, string $currency): string => Money::format($minor, $currency))->implode(' · ');
    }

    public static function subscriptionCount(string $status): int
    {
        return SaasSubscription::query()->where('status', $status)->count();
    }

    public static function aiCostMicros(?Carbon $since = null): int
    {
        return (int) AiRequest::query()->where('created_at', '>=', $since ?? now()->startOfMonth())->sum('cost_micros');
    }

    public static function usd(int $micros): string
    {
        return '$'.number_format($micros / 1_000_000, 2);
    }

    public static function failedJobsCount(): ?int
    {
        try {
            return DB::table(config('queue.failed.table', 'failed_jobs'))->count();
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string, int> plan code => tenant count */
    public static function tenantsByPlan(): array
    {
        return Tenant::query()->selectRaw('plan, count(*) as aggregate')->groupBy('plan')->orderByDesc('aggregate')->pluck('aggregate', 'plan')
            ->map(fn ($v): int => (int) $v)->all();
    }

    /**
     * Daily counts for the last N days (oldest first), zero-filled.
     *
     * @param  class-string<Model>  $model
     * @return array<string, int>
     */
    public static function dailyCounts(string $model, int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $rows = $model::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, count(*) as aggregate')
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i)->toDateString();
            $out[$day] = (int) ($rows[$day] ?? 0);
        }

        return $out;
    }

    /** @return array<string, int> */
    public static function tenantSignups(int $days = 30): array
    {
        return self::dailyCounts(Tenant::class, $days);
    }

    /** @return array<string, int> */
    public static function userSignups(int $days = 30): array
    {
        return self::dailyCounts(User::class, $days);
    }
}
