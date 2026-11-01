<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PlatformStats;
use App\Filament\Widgets\SignupsChart;
use App\Filament\Widgets\TenantsByPlanChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            PlatformStats::class,
            TenantsByPlanChart::class,
            SignupsChart::class,
        ];
    }
}
