<?php

namespace App\Filament\Widgets;

use App\Filament\Support\PlatformMetrics;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SignupsChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $maxHeight = '280px';

    public function getHeading(): ?string
    {
        return __('Signups — last 30 days');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $tenants = PlatformMetrics::tenantSignups(30);
        $users = PlatformMetrics::userSignups(30);

        return [
            'datasets' => [
                [
                    'label' => __('Tenants'),
                    'data' => array_values($tenants),
                    'borderColor' => '#D4AF6A',
                    'backgroundColor' => 'rgba(212, 175, 106, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => __('Users'),
                    'data' => array_values($users),
                    'borderColor' => '#5B8DEF',
                    'backgroundColor' => 'rgba(91, 141, 239, 0.08)',
                    'fill' => false,
                    'tension' => 0.35,
                ],
            ],
            'labels' => array_map(fn (string $d): string => Carbon::parse($d)->format('M j'), array_keys($tenants)),
        ];
    }
}
