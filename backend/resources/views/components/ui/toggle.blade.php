@props(['label' => null, 'description' => null, 'id' => null])
@php $id = $id ?? 't-'.\Illuminate\Support\Str::random(6); @endphp
<label for="{{ $id }}" class="flex cursor-pointer items-start justify-between gap-4 py-1">
    @if ($label || $description)
        <span class="min-w-0">
            @if ($label)<span class="block text-body-small font-semibold text-text-primary">{{ $label }}</span>@endif
            @if ($description)<span class="mt-0.5 block text-caption text-text-muted">{{ $description }}</span>@endif
        </span>
    @endif
    <span class="relative mt-0.5 inline-flex shrink-0">
        <input type="checkbox" id="{{ $id }}" role="switch" {{ $attributes->merge(['class' => 'peer sr-only']) }}>
        <span class="h-6 w-11 rounded-full border border-border-strong bg-surface-sunken transition duration-200 ease-out peer-checked:border-gold peer-checked:bg-gold peer-focus-visible:ring-4 peer-focus-visible:ring-gold/25"></span>
        <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-text-muted shadow transition duration-200 ease-out peer-checked:translate-x-5 peer-checked:bg-on-gold rtl:peer-checked:-translate-x-5"></span>
    </span>
</label>
