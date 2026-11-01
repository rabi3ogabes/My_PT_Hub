@props(['align' => 'end', 'width' => 'w-56', 'label' => null])
<div x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false" {{ $attributes->merge(['class' => 'relative']) }}>
    <div @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="menu" @if ($label) aria-label="{{ $label }}" @endif>{{ $trigger }}</div>
    <div x-show="open" x-cloak
        x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute z-50 mt-2 {{ $width }} {{ $align === 'start' ? 'start-0' : 'end-0' }} origin-top overflow-hidden rounded-2xl border border-border bg-surface-raised p-1.5 shadow-e3"
        role="menu" @click="open = false">
        {{ $slot }}
    </div>
</div>
