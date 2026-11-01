@props(['tabs' => [], 'active' => null])
{{-- Link tabs: $tabs = [['label' => ..., 'href' => ..., 'active' => bool]]; or Alpine panels via x-data on the parent. --}}
<nav {{ $attributes->merge(['class' => 'flex gap-1 overflow-x-auto border-b border-border']) }} aria-label="{{ __('web.common.sections') }}">
    @foreach ($tabs as $tab)
        <a href="{{ $tab['href'] }}" @if ($tab['active'] ?? false) aria-current="page" @endif
            class="relative -mb-px whitespace-nowrap px-4 py-3 text-body-small font-semibold transition duration-150 {{ ($tab['active'] ?? false) ? 'text-text-primary after:absolute after:inset-x-3 after:-bottom-px after:h-0.5 after:rounded-full after:bg-gold' : 'text-text-muted hover:text-text-primary' }}">{{ $tab['label'] }}</a>
    @endforeach
    {{ $slot }}
</nav>
