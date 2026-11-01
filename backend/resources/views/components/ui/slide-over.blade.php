@props(['name' => null, 'title' => null, 'description' => null, 'width' => 'max-w-md'])
@php $model = $attributes->wire('model')->value(); @endphp
<div
    x-data="{ open: {{ $model ? '$wire.entangle('.\Illuminate\Support\Js::from($model).')' : 'false' }} }"
    @if ($name) x-on:open-slide-over.window="if ($event.detail === @js($name)) open = true" x-on:close-slide-over.window="if ($event.detail === @js($name)) open = false" @endif
    x-on:keydown.escape.window="open = false"
    {{ $attributes->whereDoesntStartWith('wire:model') }}
>
    @isset($trigger)<span @click="open = true">{{ $trigger }}</span>@endisset
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-[80]" role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-overlay backdrop-blur-sm" @click="open = false"></div>
            <aside x-show="open" x-trap.noscroll="open"
                x-transition:enter="transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="ltr:translate-x-full rtl:-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="ltr:translate-x-full rtl:-translate-x-full"
                class="absolute inset-y-0 end-0 flex w-full {{ $width }} flex-col border-s border-border bg-surface-raised shadow-e3">
                <header class="flex items-start justify-between gap-4 border-b border-border p-6">
                    <div>
                        @if ($title)<h2 class="text-title text-text-primary">{{ $title }}</h2>@endif
                        @if ($description)<p class="mt-1 text-body-small text-text-secondary">{{ $description }}</p>@endif
                    </div>
                    <button type="button" @click="open = false" class="rounded-lg p-1.5 text-text-muted hover:bg-surface-sunken hover:text-text-primary" aria-label="{{ __('web.common.close') }}"><x-ui.icon name="x" :size="18" /></button>
                </header>
                <div class="flex-1 overflow-y-auto p-6">{{ $slot }}</div>
                @isset($footer)<footer class="flex justify-end gap-3 border-t border-border p-5">{{ $footer }}</footer>@endisset
            </aside>
        </div>
    </template>
</div>
