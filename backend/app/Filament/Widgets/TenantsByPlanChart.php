<?php

namespace App\Filament\Widgets;

use App\Filament\Support\PlatformMetrics;
use Filament\Widgets\ChartWidget;

class TenantsByPlanChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $maxHeight = '280px';

    /** Champagne gold first, then neutral luxury tones. */
    private const PALETTE = ['#D4AF6A', '#E8CF9B', '#A9853F', '#5B8DEF', '#3DBE8B', '#7D7A74', '#EF5A5A'];

    public function getHeading(): ?string
    {
        return __('Tenants by plan');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $data = PlatformMetrics::tenantsByPlan();

        return [
            'datasets' => [[
                'label' => __('Tenants'),
                'data' => array_values($data),
                'backgroundColor' => array_slice(array_pad(self::PALETTE, max(count($data), count(self::PALETTE)), '#B4B1AA'), 0, count($data)),
                'borderWidth' => 0,
            ]],
            'labels' => array_map('strval', array_keys($data)),
        ];
    }
}
