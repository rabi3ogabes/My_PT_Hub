@props(['label' => null, 'name' => null, 'options' => [], 'hint' => null, 'placeholder' => null, 'id' => null, 'optional' => false])
@php
    $model = $attributes->wire('model')->value();
    $key = $name ?? $model;
    $id = $id ?? ($key ? 'f-'.str_replace(['.', '_'], '-', $key) : null);
    $error = $key && isset($errors) ? $errors->first($key) : null;
@endphp
<x-ui.field :label="$label" :for="$id" :hint="$hint" :error="$error" :optional="$optional">
    <div class="relative">
        <select id="{{ $id }}" @if ($name) name="{{ $name }}" @endif {{ $attributes->class([
            'block h-12 w-full appearance-none rounded-xl border bg-surface ps-4 pe-10 text-body text-text-primary transition duration-150 ease-out focus:border-gold focus:outline-none focus:ring-4 focus:ring-gold/15',
            'border-danger' => $error, 'border-border' => ! $error,
        ]) }}>
            @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
            @foreach ($options as $value => $text)
                <option value="{{ $value }}">{{ $text }}</option>
            @endforeach
            {{ $slot }}
        </select>
        <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-text-muted"><x-ui.icon name="chevron-up-down" :size="16" /></span>
    </div>
</x-ui.field>
