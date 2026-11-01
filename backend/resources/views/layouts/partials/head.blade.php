{{-- Shared <head> essentials. $csrf=false on cacheable public pages (no session). --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0B0B0F">
<meta name="color-scheme" content="dark light">
@if ($csrf ?? true)<meta name="csrf-token" content="{{ csrf_token() }}">@endif
<script>
    (function () {
        try {
            var t = localStorage.getItem('au-theme') || 'dark';
            var d = t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', d);
            document.documentElement.style.colorScheme = d ? 'dark' : 'light';
        } catch (e) { document.documentElement.classList.add('dark'); }
    })();
</script>
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800{{ app()->getLocale() === 'ar' ? '|ibm-plex-sans-arabic:400,500,600,700' : '' }}&display=swap">
<link rel="icon" href="{{ route('marketing.logo') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ route('marketing.logo') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
@livewireScriptConfig
