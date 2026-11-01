@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'iconEnd' => null,
    'loading' => null,
    'block' => false,
])
@php
    $base = 'group/btn relative inline-flex items-center justify-center gap-2 whitespace-nowrap font-semibold tracking-[-0.01em] transition duration-200 ease-out select-none disabled:cursor-not-allowed disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold';
    $sizes = [
        'sm' => 'h-9 px-3.5 text-[13px] rounded-[10px]',
        'md' => 'h-11 px-5 text-sm rounded-xl',
        'lg' => 'h-13 px-7 text-[15px] rounded-[14px]',
        'icon' => 'size-10 rounded-xl',
    ];
    $variants = [
        'primary' => 'bg-gold text-on-gold shadow-[0_0_0_1px_rgba(212,175,106,0.4),0_10px_30px_-8px_rgba(212,175,106,0.55)] hover:bg-gold-light hover:shadow-[0_0_0_1px_rgba(212,175,106,0.6),0_14px_40px_-8px_rgba(212,175,106,0.7)] active:translate-y-px',
        'secondary' => 'bg-surface-raised text-text-primary border border-border hover:border-border-strong hover:bg-surface-sunken',
        'ghost' => 'text-text-secondary hover:text-text-primary hover:bg-surface-sunken',
        'danger' => 'bg-danger text-white hover:bg-danger/90 shadow-[0_8px_24px_-10px_rgba(239,90,90,0.7)]',
        'outline-gold' => 'border border-gold/50 text-gold hover:bg-gold/10',
    ];
    $classes = $base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']).($block ? ' w-full' : '');
    $iconSize = $size === 'sm' ? 16 : 18;
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="$iconSize" />@endif
        {{ $slot }}
        @if ($iconEnd)<x-ui.icon :name="$iconEnd" :size="$iconSize" class="transition-transform duration-200 group-hover/btn:translate-x-0.5 rtl:-scale-x-100 rtl:group-hover/btn:-translate-x-0.5" />@endif
    </a>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => $classes]) }} @if ($loading) wire:loading.attr="disabled" wire:target="{{ $loading }}" @endif>
        @if ($loading)
            <span wire:loading.flex wire:target="{{ $loading }}" class="absolute inset-0 hidden items-center justify-center" aria-hidden="true">
                <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
            </span>
        @endif
        <span @if ($loading) wire:loading.class="invisible" wire:target="{{ $loading }}" @endif class="inline-flex items-center gap-2">
            @if ($icon)<x-ui.icon :name="$icon" :size="$iconSize" />@endif
            {{ $slot }}
            @if ($iconEnd)<x-ui.icon :name="$iconEnd" :size="$iconSize" class="rtl:-scale-x-100" />@endif
        </span>
    </button>
@endif
