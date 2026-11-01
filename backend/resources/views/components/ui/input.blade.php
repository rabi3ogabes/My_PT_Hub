@props(['label' => null, 'name' => null, 'type' => 'text', 'hint' => null, 'icon' => null, 'optional' => false, 'id' => null])
@php
    $model = $attributes->wire('model')->value();
    $key = $name ?? $model;
    $id = $id ?? ($key ? 'f-'.str_replace(['.', '_'], '-', $key) : null);
    $error = $key && isset($errors) ? $errors->first($key) : null;
@endphp
<x-ui.field :label="$label" :for="$id" :hint="$hint" :error="$error" :optional="$optional">
    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 start-3.5 flex items-center text-text-muted"><x-ui.icon :name="$icon" :size="18" /></span>
        @endif
        @if ($type === 'password')
            <div x-data="{ show: false }" class="relative">
                <input :type="show ? 'text' : 'password'" type="password" id="{{ $id }}" @if ($name) name="{{ $name }}" @endif
                    {{ $attributes->class([
                        'block h-12 w-full rounded-xl border bg-surface px-4 pe-12 text-body text-text-primary placeholder:text-text-muted transition duration-150 ease-out',
                        'focus:border-gold focus:outline-none focus:ring-4 focus:ring-gold/15',
                        'border-danger' => $error, 'border-border' => ! $error, 'ps-11' => $icon,
                    ]) }} @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
                <button type="button" @click="show = !show" class="absolute inset-y-0 end-0 flex w-12 items-center justify-center text-text-muted hover:text-text-primary" :aria-label="show ? @js(__('web.common.hide_password')) : @js(__('web.common.show_password'))">
                    <x-ui.icon name="eye" :size="18" />
                </button>
            </div>
        @else
            <input type="{{ $type }}" id="{{ $id }}" @if ($name) name="{{ $name }}" @endif
                {{ $attributes->class([
                    'block h-12 w-full rounded-xl border bg-surface px-4 text-body text-text-primary placeholder:text-text-muted transition duration-150 ease-out',
                    'focus:border-gold focus:outline-none focus:ring-4 focus:ring-gold/15 disabled:opacity-60',
                    'border-danger' => $error, 'border-border' => ! $error, 'ps-11' => $icon,
                ]) }} @if ($error) aria-invalid="true" @endif>
        @endif
    </div>
</x-ui.field>
