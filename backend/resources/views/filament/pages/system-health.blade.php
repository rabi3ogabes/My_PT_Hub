<x-filament-panels::page>
    @php
        $checks = $this->getChecks();
        $failed = collect($checks)->where('status', 'failed')->count();
        $warnings = collect($checks)->where('status', 'warning')->count();
        $styles = [
            'ok' => ['icon' => 'heroicon-o-check-circle', 'color' => 'success', 'label' => __('Healthy')],
            'warning' => ['icon' => 'heroicon-o-exclamation-triangle', 'color' => 'warning', 'label' => __('Attention')],
            'failed' => ['icon' => 'heroicon-o-x-circle', 'color' => 'danger', 'label' => __('Failing')],
        ];
    @endphp

    <x-filament::section>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.75rem;">
            @if ($failed > 0)
                <x-filament::badge color="danger" icon="heroicon-o-x-circle">{{ trans_choice(':count check failing|:count checks failing', $failed, ['count' => $failed]) }}</x-filament::badge>
            @endif
            @if ($warnings > 0)
                <x-filament::badge color="warning" icon="heroicon-o-exclamation-triangle">{{ trans_choice(':count warning|:count warnings', $warnings, ['count' => $warnings]) }}</x-filament::badge>
            @endif
            @if ($failed === 0 && $warnings === 0)
                <x-filament::badge color="success" icon="heroicon-o-check-circle">{{ __('All systems operational') }}</x-filament::badge>
            @endif
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Checked :time', ['time' => now()->toDayDateTimeString()]) }} UTC</span>
        </div>
    </x-filament::section>

    <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(17rem,1fr));">
        @foreach ($checks as $check)
            @php($style = $styles[$check['status']] ?? $styles['failed'])
            <x-filament::section wire:key="health-{{ $check['key'] }}">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                    <div style="min-width:0;">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $check['label'] }}</p>
                        <p class="text-gray-950 dark:text-white" style="margin-top:.25rem;font-size:1.5rem;line-height:2rem;font-weight:600;letter-spacing:-0.02em;">{{ $check['value'] }}</p>
                    </div>
                    <x-filament::badge :color="$style['color']" :icon="$style['icon']">{{ $style['label'] }}</x-filament::badge>
                </div>
                @if (filled($check['detail']))
                    <p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top:.75rem;overflow-wrap:anywhere;">{{ $check['detail'] }}</p>
                @endif
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
