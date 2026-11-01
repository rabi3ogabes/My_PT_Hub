<?php

namespace App\Filament\Widgets;

use App\Domain\Tenancy\Models\Tenant;
use App\Filament\Resources\SaasSubscriptions\SaasSubscriptionResource;
use App\Filament\Support\PlatformMetrics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $failed = PlatformMetrics::failedJobsCount();
        $signups = PlatformMetrics::tenantSignups(14);

        return [
            Stat::make(__('MRR'), PlatformMetrics::formattedMrr())
                ->description(__(':count active subscriptions', ['count' => PlatformMetrics::subscriptionCount('active')]))
                ->descriptionIcon(Heroicon::OutlinedArrowTrendingUp)
                ->color('primary')
                ->url(SaasSubscriptionResource::getUrl('index')),
            Stat::make(__('Trialing'), (string) PlatformMetrics::subscriptionCount('trialing'))
                ->description(__(':count past due', ['count' => PlatformMetrics::subscriptionCount('past_due')]))
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('info'),
            Stat::make(__('Tenants'), (string) Tenant::query()->count())
                ->description(__(':count new in 14 days', ['count' => array_sum($signups)]))
                ->chart(array_values($signups))
                ->color('success'),
            Stat::make(__('AI cost this month'), PlatformMetrics::usd(PlatformMetrics::aiCostMicros()))
                ->description(__('Since :date', ['date' => now()->startOfMonth()->toFormattedDateString()]))
                ->descriptionIcon(Heroicon::OutlinedSparkles)
                ->color('warning'),
            Stat::make(__('Failed jobs'), $failed === null ? '—' : (string) $failed)
                ->description($failed === null ? __('failed_jobs table unavailable') : __('In failed_jobs'))
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($failed ? 'danger' : 'success'),
        ];
    }
}
