@props(['href' => null, 'icon' => null, 'danger' => false, 'active' => false])
@php $classes = 'flex w-full items-center gap-3 rounded-xl px-3 py-2 text-start text-body-small font-medium transition duration-150 '.($danger ? 'text-danger hover:bg-danger-soft' : ($active ? 'bg-surface-sunken text-text-primary' : 'text-text-secondary hover:bg-surface-sunken hover:text-text-primary')); @endphp
@if ($href)
    <a href="{{ $href }}" role="menuitem" {{ $attributes->merge(['class' => $classes]) }}>@if ($icon)<x-ui.icon :name="$icon" :size="16" />@endif<span class="flex-1">{{ $slot }}</span>@if ($active)<x-ui.icon name="check" :size="15" class="text-gold" />@endif</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'role' => 'menuitem', 'class' => $classes]) }}>@if ($icon)<x-ui.icon :name="$icon" :size="16" />@endif<span class="flex-1">{{ $slot }}</span>@if ($active)<x-ui.icon name="check" :size="15" class="text-gold" />@endif</button>
@endif
