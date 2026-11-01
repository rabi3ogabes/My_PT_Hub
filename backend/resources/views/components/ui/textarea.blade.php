@props(['label' => null, 'name' => null, 'hint' => null, 'rows' => 4, 'id' => null, 'optional' => false])
@php
    $model = $attributes->wire('model')->value();
    $key = $name ?? $model;
    $id = $id ?? ($key ? 'f-'.str_replace(['.', '_'], '-', $key) : null);
    $error = $key && isset($errors) ? $errors->first($key) : null;
@endphp
<x-ui.field :label="$label" :for="$id" :hint="$hint" :error="$error" :optional="$optional">
    <textarea id="{{ $id }}" rows="{{ $rows }}" @if ($name) name="{{ $name }}" @endif {{ $attributes->class([
        'block w-full rounded-xl border bg-surface px-4 py-3 text-body text-text-primary placeholder:text-text-muted transition duration-150 ease-out focus:border-gold focus:outline-none focus:ring-4 focus:ring-gold/15',
        'border-danger' => $error, 'border-border' => ! $error,
    ]) }}>{{ $slot }}</textarea>
</x-ui.field>
