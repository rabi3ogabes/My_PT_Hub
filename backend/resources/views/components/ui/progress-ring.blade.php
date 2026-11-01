@props(['value' => 0, 'size' => 64, 'stroke' => 6, 'label' => null])
@php
    $value = max(0, min(100, (float) $value));
    $r = ($size - $stroke) / 2;
    $c = 2 * M_PI * $r;
    $offset = $c * (1 - $value / 100);
@endphp
<div {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center']) }} role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) $value }}" @if ($label) aria-label="{{ $label }}" @endif>
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="-rotate-90">
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none" stroke="currentColor" class="text-border" stroke-width="{{ $stroke }}"/>
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none" stroke="url(#au-ring-gold)" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-dasharray="{{ $c }}" stroke-dashoffset="{{ $offset }}" class="transition-[stroke-dashoffset] duration-700 ease-out"/>
        <defs><linearGradient id="au-ring-gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#E8CF9B"/><stop offset="100%" stop-color="#A9853F"/></linearGradient></defs>
    </svg>
    <span class="absolute text-label font-bold tabular-nums text-text-primary">{{ trim($slot) !== '' ? $slot : ((int) $value).'%' }}</span>
</div>
