@props(['active' => false, 'href' => null, 'icon' => null])
@php
    $classes = 'inline-flex h-9 items-center gap-2 rounded-full border px-4 text-[13px] font-semibold transition duration-150 ease-out '.($active ? 'border-gold/60 bg-gold/10 text-gold' : 'border-border text-text-secondary hover:border-border-strong hover:text-text-primary');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if ($active) aria-current="true" @endif>@if ($icon)<x-ui.icon :name="$icon" :size="15" />@endif{{ $slot }}</a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }} aria-pressed="{{ $active ? 'true' : 'false' }}">@if ($icon)<x-ui.icon :name="$icon" :size="15" />@endif{{ $slot }}</button>
@endif
