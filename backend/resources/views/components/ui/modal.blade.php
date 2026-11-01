@props(['name' => null, 'title' => null, 'description' => null, 'maxWidth' => 'md', 'icon' => null, 'tone' => 'gold'])
@php
    $model = $attributes->wire('model')->value();
    $widths = ['sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg', 'xl' => 'sm:max-w-2xl'];
    $tones = ['gold' => 'bg-gold/10 text-gold', 'danger' => 'bg-danger-soft text-danger', 'success' => 'bg-success-soft text-success'];
    $titleId = 'm-'.\Illuminate\Support\Str::random(6);
@endphp
<div
    x-data="{ open: {{ $model ? '$wire.entangle('.\Illuminate\Support\Js::from($model).')' : 'false' }} }"
    @if ($name) x-on:open-modal.window="if ($event.detail === @js($name) || $event.detail?.name === @js($name)) open = true" x-on:close-modal.window="if ($event.detail === @js($name) || $event.detail?.name === @js($name)) open = false" @endif
    x-on:keydown.escape.window="open = false"
    x-effect="document.documentElement.classList.toggle('overflow-hidden', open)"
    {{ $attributes->whereDoesntStartWith('wire:model') }}
>
    @isset($trigger)
        <span @click="open = true">{{ $trigger }}</span>
    @endisset
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="{{ $titleId }}">
            <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-overlay backdrop-blur-sm" @click="open = false"></div>
            <div x-show="open" x-trap.noscroll="open"
                x-transition:enter="transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-2"
                class="relative w-full {{ $widths[$maxWidth] ?? $widths['md'] }} rounded-[24px] border border-border bg-surface-raised p-6 shadow-e3 sm:p-7">
                <button type="button" @click="open = false" class="absolute end-4 top-4 rounded-lg p-1.5 text-text-muted hover:bg-surface-sunken hover:text-text-primary" aria-label="{{ __('web.common.close') }}"><x-ui.icon name="x" :size="18" /></button>
                @if ($icon)
                    <span class="mb-5 flex size-11 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['gold'] }}"><x-ui.icon :name="$icon" :size="22" /></span>
                @endif
                @if ($title)<h2 id="{{ $titleId }}" class="text-h3 text-text-primary">{{ $title }}</h2>@endif
                @if ($description)<p class="mt-2 text-body-small text-text-secondary">{{ $description }}</p>@endif
                <div class="{{ $title || $description ? 'mt-6' : '' }}">{{ $slot }}</div>
                @isset($footer)
                    <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">{{ $footer }}</div>
                @endisset
            </div>
        </div>
    </template>
</div>
