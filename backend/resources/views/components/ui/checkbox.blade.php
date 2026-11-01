@props(['label' => null, 'id' => null, 'name' => null])
@php
    $model = $attributes->wire('model')->value();
    $key = $name ?? $model;
    $id = $id ?? ($key ? 'c-'.str_replace(['.', '_'], '-', $key) : 'c-'.\Illuminate\Support\Str::random(6));
    $error = $key && isset($errors) ? $errors->first($key) : null;
@endphp
<div>
    <label for="{{ $id }}" class="group flex cursor-pointer items-start gap-3">
        <span class="relative mt-0.5 inline-flex">
            <input type="checkbox" id="{{ $id }}" @if ($name) name="{{ $name }}" @endif {{ $attributes->merge(['class' => 'peer size-5 appearance-none rounded-md border border-border-strong bg-surface transition duration-150 checked:border-gold checked:bg-gold focus-visible:ring-4 focus-visible:ring-gold/25']) }}>
            <x-ui.icon name="check" :size="14" :stroke="2.5" class="pointer-events-none absolute inset-0 m-auto text-on-gold opacity-0 peer-checked:opacity-100" />
        </span>
        <span class="text-body-small text-text-secondary">{{ $label ?? $slot }}</span>
    </label>
    @if ($error)<p class="mt-1 text-caption text-danger" role="alert">{{ $error }}</p>@endif
</div>
