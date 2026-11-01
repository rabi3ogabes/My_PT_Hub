@props(['label' => null, 'for' => null, 'hint' => null, 'error' => null, 'optional' => false])
<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="flex items-center justify-between text-label text-text-primary">
            <span>{{ $label }}</span>
            @if ($optional)<span class="text-caption font-medium text-text-muted">{{ __('web.common.optional') }}</span>@endif
        </label>
    @endif
    {{ $slot }}
    @if ($error)
        <p class="flex items-center gap-1.5 text-caption text-danger" role="alert"><x-ui.icon name="exclamation" :size="14" />{{ $error }}</p>
    @elseif ($hint)
        <p class="text-caption text-text-muted">{{ $hint }}</p>
    @endif
</div>
