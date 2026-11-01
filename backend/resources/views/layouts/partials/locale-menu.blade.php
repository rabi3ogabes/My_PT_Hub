{{-- Locale switcher. $links: locale => URL (marketing alternates); defaults to the session switch route. --}}
@php
    $current = app()->getLocale();
    $links = $links ?? null;
    $align = $align ?? 'end';
    $compact = $compact ?? false;
@endphp
<x-ui.dropdown :align="$align" width="w-44" :label="__('web.common.language')">
    <x-slot:trigger>
        <button type="button" class="inline-flex h-10 items-center gap-1.5 rounded-xl px-2.5 text-body-small font-semibold text-text-secondary transition hover:bg-surface-sunken hover:text-text-primary">
            <x-ui.icon name="language" :size="18" />
            <span class="{{ $compact ? 'sr-only' : '' }} uppercase">{{ $current }}</span>
        </button>
    </x-slot:trigger>
    @foreach (\App\View\Locales::all() as $locale)
        <x-ui.dropdown-item :href="$links[$locale] ?? route('locale.switch', $locale)" :active="$locale === $current" hreflang="{{ $locale }}" lang="{{ $locale }}">{{ \App\View\Locales::name($locale) }}</x-ui.dropdown-item>
    @endforeach
</x-ui.dropdown>
