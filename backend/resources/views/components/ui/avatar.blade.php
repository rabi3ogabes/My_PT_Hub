@props(['name' => '', 'src' => null, 'size' => 'md'])
@php
    $sizes = ['xs' => 'size-6 text-[10px]', 'sm' => 'size-8 text-xs', 'md' => 'size-10 text-sm', 'lg' => 'size-14 text-lg', 'xl' => 'size-20 text-2xl'];
    $initials = collect(preg_split('/\s+/u', trim((string) $name)) ?: [])->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp
@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}" loading="lazy" decoding="async" {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover ring-1 ring-border '.($sizes[$size] ?? $sizes['md'])]) }}>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-gold-light/25 to-gold-dark/25 font-bold text-gold ring-1 ring-gold/25 '.($sizes[$size] ?? $sizes['md'])]) }} aria-hidden="{{ $name ? 'false' : 'true' }}" @if ($name) role="img" aria-label="{{ $name }}" @endif>{{ $initials ?: '·' }}</span>
@endif
