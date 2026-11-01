@props(['seo', 'csrf' => false])
@php
    /** @var \App\View\Seo $seo */
    $locale = app()->getLocale();
    $features = \App\View\Marketing\Catalog::FEATURES;
    $solutions = \App\View\Marketing\Catalog::SOLUTIONS;
    $r = fn (string $name, array $params = []) => route($name, $params + ['locale' => $locale]);
    $iosApp = config('brand.ios_app_id');
    $resources = [
        ['marketing.calculators.index', 'calculator', 'calculators'],
        ['marketing.exercises.index', 'bolt', 'exercises'],
        ['marketing.templates.index', 'document', 'templates'],
        ['marketing.coaches.index', 'map-pin', 'coaches'],
        ['marketing.blog.index', 'book', 'blog'],
        ['marketing.switch', 'refresh', 'switch'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ \App\View\Locales::dir() }}" class="dark">
<head>
    @include('layouts.partials.head', ['csrf' => $csrf])
    <title>{{ $seo->fullTitle() }}</title>
    @if ($seo->description)<meta name="description" content="{{ $seo->description }}">@endif
    <meta name="robots" content="{{ $seo->noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large' }}">
    @if ($seo->canonical)<link rel="canonical" href="{{ $seo->canonical }}">@endif
    @foreach ($seo->alternates as $hl => $href)
        <link rel="alternate" hreflang="{{ $hl }}" href="{{ $href }}">
    @endforeach
    @if (isset($seo->alternates['en']))<link rel="alternate" hreflang="x-default" href="{{ $seo->alternates['en'] }}">@endif

    <meta property="og:site_name" content="{{ config('brand.name') }}">
    <meta property="og:type" content="{{ $seo->type }}">
    <meta property="og:title" content="{{ $seo->title }}">
    @if ($seo->description)<meta property="og:description" content="{{ $seo->description }}">@endif
    <meta property="og:url" content="{{ $seo->canonical ?? url()->current() }}">
    <meta property="og:image" content="{{ $seo->imageUrl() }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ \App\View\Locales::og() }}">
    @foreach (\App\View\Locales::all() as $l)
        @if ($l !== $locale)<meta property="og:locale:alternate" content="{{ \App\View\Locales::og($l) }}">@endif
    @endforeach
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo->title }}">
    @if ($seo->description)<meta name="twitter:description" content="{{ $seo->description }}">@endif
    <meta name="twitter:image" content="{{ $seo->imageUrl() }}">

    @if ($iosApp)
        <meta name="apple-itunes-app" content="app-id={{ $iosApp }}, app-argument={{ $seo->canonical ?? url()->current() }}">
    @endif
    @if (config('brand.android_package'))
        <meta name="google-play-app" content="app-id={{ config('brand.android_package') }}">
    @endif

    <script type="application/ld+json">{!! $seo->jsonLd() !!}</script>
    {{ $head ?? '' }}
</head>
<body class="min-h-dvh bg-background font-sans text-text-primary antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-gold focus:px-4 focus:py-2 focus:text-on-gold">{{ __('web.common.skip_to_content') }}</a>

    <header x-data="{ menu: null, mobile: false, scrolled: false }" x-init="scrolled = window.scrollY > 8" @scroll.window.passive="scrolled = window.scrollY > 8" @keydown.escape.window="menu = null; mobile = false"
        class="sticky top-0 z-50 transition-colors duration-300" :class="scrolled || mobile ? 'au-glass border-b border-border' : 'border-b border-transparent'">
        <div class="au-container flex h-16 items-center gap-6 lg:h-[72px]">
            <x-ui.logo :href="$r('marketing.home')" />

            <nav class="hidden flex-1 items-center gap-1 lg:flex" aria-label="{{ __('marketing.nav.main') }}" @mouseleave="menu = null">
                @foreach (['product' => __('marketing.nav.product'), 'solutions' => __('marketing.nav.solutions'), 'resources' => __('marketing.nav.resources')] as $key => $label)
                    <div class="relative" @mouseenter="menu = '{{ $key }}'">
                        <button type="button" @click="menu = menu === '{{ $key }}' ? null : '{{ $key }}'" :aria-expanded="(menu === '{{ $key }}').toString()"
                            class="inline-flex h-10 items-center gap-1 rounded-xl px-3.5 text-body-small font-semibold text-text-secondary transition hover:text-text-primary" :class="menu === '{{ $key }}' && 'text-text-primary'">
                            {{ $label }} <x-ui.icon name="chevron-down" :size="14" class="transition-transform duration-200" ::class="menu === '{{ $key }}' && 'rotate-180'" />
                        </button>
                    </div>
                @endforeach
                <a href="{{ $r('marketing.pricing') }}" class="inline-flex h-10 items-center rounded-xl px-3.5 text-body-small font-semibold text-text-secondary transition hover:text-text-primary">{{ __('marketing.nav.pricing') }}</a>

                {{-- Mega menu --}}
                <div x-show="menu" x-cloak x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition duration-100 ease-in" x-transition:leave-end="opacity-0"
                    class="absolute inset-x-0 top-full border-b border-border bg-surface/95 shadow-e3 backdrop-blur-xl" @click.outside="menu = null">
                    <div class="au-container grid gap-8 py-8 lg:grid-cols-[1fr_280px]">
                        <div x-show="menu === 'product'" class="grid grid-cols-3 gap-2">
                            @foreach ($features as $slug => $icon)
                                <a href="{{ $r('marketing.features.show', ['module' => $slug]) }}" class="group flex gap-3 rounded-2xl p-3 transition hover:bg-surface-sunken">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-border bg-surface-raised text-gold transition group-hover:border-gold/40"><x-ui.icon :name="$icon" :size="18" /></span>
                                    <span><span class="block text-body-small font-semibold text-text-primary">{{ __('marketing.features.items.'.$slug.'.name') }}</span><span class="mt-0.5 block text-caption text-text-muted">{{ __('marketing.features.items.'.$slug.'.short') }}</span></span>
                                </a>
                            @endforeach
                        </div>
                        <div x-show="menu === 'solutions'" class="grid grid-cols-3 gap-2">
                            @foreach ($solutions as $slug => $icon)
                                <a href="{{ $r('marketing.solutions.show', ['segment' => $slug]) }}" class="group flex gap-3 rounded-2xl p-3 transition hover:bg-surface-sunken">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-border bg-surface-raised text-gold transition group-hover:border-gold/40"><x-ui.icon :name="$icon" :size="18" /></span>
                                    <span><span class="block text-body-small font-semibold text-text-primary">{{ __('marketing.solutions.items.'.$slug.'.name') }}</span><span class="mt-0.5 block text-caption text-text-muted">{{ __('marketing.solutions.items.'.$slug.'.short') }}</span></span>
                                </a>
                            @endforeach
                        </div>
                        <div x-show="menu === 'resources'" class="grid grid-cols-3 gap-2">
                            @foreach ($resources as [$route, $icon, $key])
                                <a href="{{ $r($route) }}" class="group flex gap-3 rounded-2xl p-3 transition hover:bg-surface-sunken">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-border bg-surface-raised text-gold transition group-hover:border-gold/40"><x-ui.icon :name="$icon" :size="18" /></span>
                                    <span><span class="block text-body-small font-semibold text-text-primary">{{ __('marketing.nav.res.'.$key) }}</span><span class="mt-0.5 block text-caption text-text-muted">{{ __('marketing.nav.res.'.$key.'_desc') }}</span></span>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('register') }}" class="group relative hidden overflow-hidden rounded-[22px] border border-gold/25 bg-[#0B0B0F] p-6 text-[#F5F3EE] lg:block">
                            <div class="absolute inset-0 bg-[radial-gradient(30rem_16rem_at_100%_0%,rgba(212,175,106,0.25),transparent_60%)]" aria-hidden="true"></div>
                            <p class="relative au-eyebrow">{{ __('marketing.nav.promo_eyebrow') }}</p>
                            <p class="relative mt-3 text-title font-bold">{{ __('marketing.nav.promo_title') }}</p>
                            <p class="relative mt-2 text-caption text-[#B4B1AA]">{{ __('marketing.cta.trust') }}</p>
                            <span class="relative mt-5 inline-flex items-center gap-1.5 text-body-small font-semibold text-[#D4AF6A]">{{ __('marketing.cta.start_trial') }} <x-ui.icon name="arrow-right" :size="15" class="transition group-hover:translate-x-0.5 rtl:-scale-x-100" /></span>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="ms-auto flex items-center gap-1">
                @include('layouts.partials.locale-menu', ['links' => $seo->alternates ?: null, 'compact' => true])
                @include('layouts.partials.theme-toggle')
                <a href="{{ route('login') }}" class="hidden h-10 items-center rounded-xl px-3.5 text-body-small font-semibold text-text-secondary transition hover:text-text-primary sm:inline-flex">{{ __('marketing.nav.sign_in') }}</a>
                <x-ui.button :href="route('register')" size="sm" class="hidden sm:inline-flex">{{ __('marketing.cta.start_trial') }}</x-ui.button>
                <button type="button" class="inline-flex size-10 items-center justify-center rounded-xl text-text-primary lg:hidden" @click="mobile = !mobile" :aria-expanded="mobile.toString()" aria-controls="mobile-nav" aria-label="{{ __('marketing.nav.menu') }}">
                    <x-ui.icon name="menu" :size="20" x-show="!mobile" />
                    <x-ui.icon name="x" :size="20" x-show="mobile" x-cloak />
                </button>
            </div>
        </div>

        {{-- Mobile navigation --}}
        <div id="mobile-nav" x-show="mobile" x-cloak x-collapse class="border-t border-border lg:hidden">
            <nav class="au-container max-h-[calc(100dvh-4rem)] space-y-6 overflow-y-auto py-6" aria-label="{{ __('marketing.nav.main') }}">
                @foreach (['product' => [$features, 'marketing.features.show', 'module', 'features'], 'solutions' => [$solutions, 'marketing.solutions.show', 'segment', 'solutions']] as $key => [$items, $route, $param, $group])
                    <div>
                        <p class="au-eyebrow">{{ __('marketing.nav.'.$key) }}</p>
                        <ul class="mt-3 grid grid-cols-2 gap-1">
                            @foreach ($items as $slug => $icon)
                                <li><a href="{{ $r($route, [$param => $slug]) }}" class="block rounded-xl px-2 py-2 text-body-small font-medium text-text-secondary hover:bg-surface-sunken hover:text-text-primary">{{ __('marketing.'.$group.'.items.'.$slug.'.name') }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
                <div>
                    <p class="au-eyebrow">{{ __('marketing.nav.resources') }}</p>
                    <ul class="mt-3 grid grid-cols-2 gap-1">
                        @foreach ($resources as [$route, $icon, $key])
                            <li><a href="{{ $r($route) }}" class="block rounded-xl px-2 py-2 text-body-small font-medium text-text-secondary hover:bg-surface-sunken hover:text-text-primary">{{ __('marketing.nav.res.'.$key) }}</a></li>
                        @endforeach
                        <li><a href="{{ $r('marketing.pricing') }}" class="block rounded-xl px-2 py-2 text-body-small font-medium text-text-secondary hover:bg-surface-sunken hover:text-text-primary">{{ __('marketing.nav.pricing') }}</a></li>
                    </ul>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.button :href="route('login')" variant="secondary">{{ __('marketing.nav.sign_in') }}</x-ui.button>
                    <x-ui.button :href="route('register')">{{ __('marketing.cta.start_trial') }}</x-ui.button>
                </div>
            </nav>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="relative mt-24 border-t border-border">
        <div class="au-container py-16">
            <div class="grid gap-12 lg:grid-cols-[1.4fr_repeat(4,1fr)]">
                <div class="max-w-xs">
                    <x-ui.logo :href="$r('marketing.home')" />
                    <p class="mt-4 text-body-small text-text-secondary">{{ __('marketing.footer.tagline') }}</p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        @include('marketing.partials.store-badges', ['compact' => true])
                    </div>
                </div>
                <div>
                    <p class="text-label text-text-primary">{{ __('marketing.nav.product') }}</p>
                    <ul class="mt-4 space-y-2.5 text-body-small">
                        @foreach (array_slice($features, 0, 6, true) as $slug => $icon)
                            <li><a class="text-text-secondary hover:text-text-primary" href="{{ $r('marketing.features.show', ['module' => $slug]) }}">{{ __('marketing.features.items.'.$slug.'.name') }}</a></li>
                        @endforeach
                        <li><a class="text-text-secondary hover:text-text-primary" href="{{ $r('marketing.pricing') }}">{{ __('marketing.nav.pricing') }}</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-label text-text-primary">{{ __('marketing.nav.solutions') }}</p>
                    <ul class="mt-4 space-y-2.5 text-body-small">
                        @foreach ($solutions as $slug => $icon)
                            <li><a class="text-text-secondary hover:text-text-primary" href="{{ $r('marketing.solutions.show', ['segment' => $slug]) }}">{{ __('marketing.solutions.items.'.$slug.'.name') }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-label text-text-primary">{{ __('marketing.nav.resources') }}</p>
                    <ul class="mt-4 space-y-2.5 text-body-small">
                        @foreach ($resources as [$route, $icon, $key])
                            <li><a class="text-text-secondary hover:text-text-primary" href="{{ $r($route) }}">{{ __('marketing.nav.res.'.$key) }}</a></li>
                        @endforeach
                        @foreach (array_slice(\App\View\Marketing\Catalog::COMPETITORS, 0, 3, true) as $slug => $name)
                            <li><a class="text-text-secondary hover:text-text-primary" href="{{ $r('marketing.compare', ['competitor' => $slug]) }}">{{ __('marketing.compare.link', ['name' => $name]) }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-label text-text-primary">{{ __('marketing.footer.company') }}</p>
                    <ul class="mt-4 space-y-2.5 text-body-small">
                        <li><a class="text-text-secondary hover:text-text-primary" href="{{ $r('marketing.about') }}">{{ __('marketing.footer.about') }}</a></li>
                        <li><a class="text-text-secondary hover:text-text-primary" href="{{ $r('marketing.contact') }}">{{ __('marketing.footer.contact') }}</a></li>
                        @foreach (\App\View\Marketing\Catalog::LEGAL as $page)
                            <li><a class="text-text-secondary hover:text-text-primary" href="{{ $r('marketing.legal', ['page' => $page]) }}">{{ __('marketing.legal.'.$page.'.title') }}</a></li>
                        @endforeach
                        <li><button type="button" class="text-text-secondary hover:text-text-primary" @click="window.dispatchEvent(new CustomEvent('au-open-consent'))" x-data>{{ __('marketing.consent.settings') }}</button></li>
                    </ul>
                </div>
            </div>
            <div class="mt-14 flex flex-col gap-4 border-t border-border pt-8 text-caption text-text-muted sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ date('Y') }} {{ config('brand.name') }}. {{ __('marketing.footer.rights') }}</p>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                    @foreach (\App\View\Locales::all() as $l)
                        <a href="{{ $seo->alternates[$l] ?? route('marketing.home', ['locale' => $l]) }}" hreflang="{{ $l }}" lang="{{ $l }}" class="{{ $l === $locale ? 'text-text-primary' : 'hover:text-text-primary' }}">{{ \App\View\Locales::name($l) }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>

    {{-- Cookie consent (privacy-preserving default: only strictly necessary storage until opt-in) --}}
    <div x-data="cookieConsent" x-show="visible" x-cloak x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed inset-x-4 bottom-4 z-[70] mx-auto max-w-xl rounded-[22px] border border-border bg-surface-raised/95 p-5 shadow-e3 backdrop-blur-xl sm:inset-x-auto sm:end-6 sm:start-auto" role="dialog" aria-live="polite" aria-label="{{ __('marketing.consent.title') }}">
        <p class="text-body-small font-semibold text-text-primary">{{ __('marketing.consent.title') }}</p>
        <p class="mt-1.5 text-caption leading-relaxed text-text-secondary">{{ __('marketing.consent.body') }} <a href="{{ $r('marketing.legal', ['page' => 'cookies']) }}" class="text-gold underline underline-offset-2">{{ __('marketing.consent.learn') }}</a></p>
        <div x-show="details" x-collapse class="mt-4 space-y-3 rounded-2xl border border-border p-4">
            <div class="flex items-center justify-between gap-3 text-caption"><span class="font-semibold text-text-primary">{{ __('marketing.consent.necessary') }}</span><span class="text-text-muted">{{ __('marketing.consent.always_on') }}</span></div>
            <label class="flex items-center justify-between gap-3 text-caption"><span class="font-semibold text-text-primary">{{ __('marketing.consent.analytics') }}</span><input type="checkbox" x-model="analytics" class="size-4 accent-[#D4AF6A]"></label>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <x-ui.button size="sm" @click="save(true)">{{ __('marketing.consent.accept') }}</x-ui.button>
            <x-ui.button size="sm" variant="secondary" @click="save(false)">{{ __('marketing.consent.reject') }}</x-ui.button>
            <button type="button" x-show="!details" @click="details = true" class="ms-auto text-caption font-semibold text-text-muted hover:text-text-primary">{{ __('marketing.consent.customize') }}</button>
            <button type="button" x-show="details" @click="save(analytics)" class="ms-auto text-caption font-semibold text-gold">{{ __('marketing.consent.save') }}</button>
        </div>
    </div>
    <x-ui.toasts />
</body>
</html>
