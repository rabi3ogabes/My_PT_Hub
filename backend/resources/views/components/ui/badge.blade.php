@props(['tone' => 'neutral', 'dot' => false])
@php
    $tones = [
        'neutral' => 'bg-surface-sunken text-text-secondary border-border',
        'gold' => 'bg-gold/10 text-gold border-gold/30',
        'success' => 'bg-success-soft text-success border-success/25',
        'warning' => 'bg-warning-soft text-warning border-warning/25',
        'danger' => 'bg-danger-soft text-danger border-danger/25',
        'info' => 'bg-info-soft text-info border-info/25',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-caption font-semibold whitespace-nowrap '.($tones[$tone] ?? $tones['neutral'])]) }}>
    @if ($dot)<span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>@endif
    {{ $slot }}
</span>
