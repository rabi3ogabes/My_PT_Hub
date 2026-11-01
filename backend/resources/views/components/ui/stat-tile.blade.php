@props(['label', 'value', 'icon' => null, 'hint' => null, 'tone' => 'neutral', 'href' => null])
@php
    $tones = ['neutral' => 'text-text-secondary bg-surface-sunken', 'gold' => 'text-gold bg-gold/10', 'danger' => 'text-danger bg-danger-soft', 'success' => 'text-success bg-success-soft', 'info' => 'text-info bg-info-soft', 'warning' => 'text-warning bg-warning-soft'];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class(['group block rounded-[20px] border border-border bg-surface p-5 transition duration-200 ease-out', 'hover:border-border-strong hover:shadow-e2' => $href]) }}>
    <div class="flex items-center justify-between">
        <span class="text-label text-text-muted">{{ $label }}</span>
        @if ($icon)<span class="flex size-8 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['neutral'] }}"><x-ui.icon :name="$icon" :size="16" /></span>@endif
    </div>
    <div class="mt-4 text-[2rem] leading-none font-extrabold tracking-[-0.03em] text-text-primary tabular-nums">{{ $value }}</div>
    @if ($hint)<div class="mt-2 text-caption text-text-muted">{{ $hint }}</div>@endif
</{{ $tag }}>
