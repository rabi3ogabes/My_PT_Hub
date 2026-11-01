<?php

namespace App\Filament\Widgets;

use App\Domain\Ai\Models\AiRequest;
use App\Filament\Support\PlatformMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Shown above the AI usage table (not on the dashboard). */
class AiUsageStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $month = AiRequest::query()->where('created_at', '>=', now()->startOfMonth());
        $total = (clone $month)->count();
        $errors = (clone $month)->where('status', '!=', 'ok')->count();
        $topFeature = (clone $month)->selectRaw('feature, sum(cost_micros) as cost')->groupBy('feature')->orderByDesc('cost')->first();
        $topTenant = (clone $month)->whereNotNull('ai_requests.tenant_id')
            ->join('tenants', 'tenants.id', '=', 'ai_requests.tenant_id')
            ->selectRaw('tenants.name as name, sum(ai_requests.cost_micros) as cost')
            ->groupBy('tenants.name')->orderByDesc('cost')->first();

        return [
            Stat::make(__('AI cost this month'), PlatformMetrics::usd(PlatformMetrics::aiCostMicros()))->color('warning'),
            Stat::make(__('Requests this month'), number_format($total))
                ->description(__(':count tokens', ['count' => number_format((int) (clone $month)->sum('tokens_in') + (int) (clone $month)->sum('tokens_out'))])),
            Stat::make(__('Error / refusal rate'), $total > 0 ? round($errors * 100 / $total, 1).'%' : '0%')
                ->color($total > 0 && $errors / $total > 0.05 ? 'danger' : 'success'),
            Stat::make(__('Top cost'), $topFeature?->getAttribute('feature') ?? '—')
                ->description($topTenant ? __('Top tenant: :name', ['name' => $topTenant->getAttribute('name')]) : null),
        ];
    }
}
