<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\View\Locales::dir() }}" class="dark">
<head>
    @include('layouts.partials.head')
    <title>{{ isset($title) ? $title.' · '.config('brand.name') : config('brand.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="min-h-dvh bg-background font-sans text-text-primary antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-gold focus:px-4 focus:py-2 focus:text-on-gold">{{ __('web.common.skip_to_content') }}</a>
    <div class="grid min-h-dvh lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
        {{-- Editorial panel --}}
        <aside class="relative hidden overflow-hidden border-e border-border bg-[#0B0B0F] text-[#F5F3EE] lg:flex lg:flex-col" aria-hidden="false">
            <div class="absolute inset-0" aria-hidden="true">
                <div class="absolute inset-0 bg-[radial-gradient(70rem_40rem_at_100%_0%,rgba(212,175,106,0.20),transparent_60%),radial-gradient(50rem_30rem_at_0%_100%,rgba(212,175,106,0.10),transparent_60%)]"></div>
                <svg class="absolute -end-40 top-1/2 h-[46rem] w-[46rem] -translate-y-1/2 opacity-[0.55]" viewBox="0 0 600 600" fill="none">
                    <defs><linearGradient id="au-arc" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#F1DDB1" stop-opacity=".9"/><stop offset="1" stop-color="#9C7A36" stop-opacity=".1"/></linearGradient></defs>
                    @foreach ([280, 236, 192, 148, 104] as $i => $r)
                        <circle cx="300" cy="300" r="{{ $r }}" stroke="url(#au-arc)" stroke-width="{{ $i === 0 ? 1.2 : 0.8 }}" stroke-dasharray="{{ $i % 2 ? '2 10' : '' }}" style="transform-origin: 300px 300px; animation: spin {{ 80 + $i * 20 }}s linear infinite{{ $i % 2 ? ' reverse' : '' }}"/>
                    @endforeach
                    <path d="M210 380 300 180l90 200" stroke="url(#au-arc)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M243 312h114" stroke="url(#au-arc)" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:64px_64px] [mask-image:radial-gradient(ellipse_at_30%_40%,black,transparent_70%)]"></div>
            </div>
            <div class="relative flex flex-1 flex-col justify-between p-12 xl:p-16">
                <x-ui.logo :href="route('marketing.home', ['locale' => app()->getLocale()])" class="!text-[#F5F3EE]" />
                <div class="max-w-lg">
                    <p class="au-eyebrow">{{ __('web.auth.panel_eyebrow') }}</p>
                    <h2 class="mt-5 text-[2.75rem] leading-[1.05] font-extrabold tracking-[-0.035em]">{{ __('web.auth.panel_title') }}</h2>
                    <ul class="mt-10 space-y-4 text-[15px] text-[#B4B1AA]">
                        @foreach (['speed', 'ai', 'languages', 'billing'] as $point)
                            <li class="flex items-start gap-3"><span class="mt-0.5 flex size-5 items-center justify-center rounded-full bg-[#D4AF6A]/15 text-[#D4AF6A]"><x-ui.icon name="check" :size="12" :stroke="2.5" /></span>{{ __('web.auth.panel_points.'.$point) }}</li>
                        @endforeach
                    </ul>
                </div>
                <figure class="max-w-lg rounded-[24px] border border-white/10 bg-white/[0.03] p-6 backdrop-blur">
                    <blockquote class="text-[17px] leading-relaxed text-[#F5F3EE]">“{{ __('web.auth.promise_quote') }}”</blockquote>
                    <figcaption class="mt-4 flex items-center gap-3 text-[13px] text-[#7D7A74]">
                        <x-ui.logo :size="22" :wordmark="false" />
                        <span>{{ __('web.auth.promise_by', ['brand' => config('brand.name')]) }}</span>
                    </figcaption>
                </figure>
            </div>
        </aside>

        {{-- Form panel --}}
        <div class="relative flex min-h-dvh flex-col">
            <header class="flex items-center justify-between px-6 py-5 sm:px-10">
                <x-ui.logo :href="route('marketing.home', ['locale' => app()->getLocale()])" class="lg:invisible" />
                <div class="flex items-center gap-1">
                    @include('layouts.partials.locale-menu')
                    @include('layouts.partials.theme-toggle')
                </div>
            </header>
            <main id="main" class="flex flex-1 items-center justify-center px-6 pb-16 sm:px-10">
                <div class="w-full max-w-[420px] animate-fade-up">
                    {{ $slot }}
                </div>
            </main>
            <footer class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 px-6 pb-8 text-caption text-text-muted">
                <a class="hover:text-text-primary" href="{{ route('marketing.legal', ['locale' => app()->getLocale(), 'page' => 'terms']) }}">{{ __('marketing.footer.terms') }}</a>
                <a class="hover:text-text-primary" href="{{ route('marketing.legal', ['locale' => app()->getLocale(), 'page' => 'privacy']) }}">{{ __('marketing.footer.privacy') }}</a>
                <span>© {{ date('Y') }} {{ config('brand.name') }}</span>
            </footer>
        </div>
    </div>
    <x-ui.toasts />
</body>
</html>
