@php
    /** @var \App\Domain\Tenancy\Models\Tenant|null $tenant */
    $tenant ??= app(\App\Domain\Shared\Tenancy\TenantContext::class)->get();
    $user = auth()->user();
    $subscription = $tenant?->subscription;
    $trialDays = ($subscription && $subscription->status === 'trialing' && $subscription->trial_ends_at?->isFuture())
        ? max(1, (int) ceil(now()->diffInHours($subscription->trial_ends_at) / 24)) : null;
    $groups = \App\View\AppNav::groups();
    $palette = \App\View\AppNav::palette();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\View\Locales::dir() }}" class="dark">
<head>
    @include('layouts.partials.head')
    <meta name="au-user" content="{{ $user?->id }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ isset($title) ? $title.' · '.($tenant?->name ?? config('brand.name')) : ($tenant?->name ?? config('brand.name')) }}</title>
    @vite('resources/js/echo.js')
    @if ($tenant?->brand_color && preg_match('/^#[0-9a-fA-F]{6}$/', $tenant->brand_color))
        <style>:root { --au-tenant: {{ $tenant->brand_color }}; }</style>
    @endif
</head>
<body class="min-h-dvh bg-background font-sans text-text-primary antialiased"
    x-data="{ mobileNav: false, collapsed: (() => { try { return localStorage.getItem('au-sidebar') === '1' } catch (e) { return false } })() }"
    x-init="$watch('collapsed', v => { try { localStorage.setItem('au-sidebar', v ? '1' : '0') } catch (e) {} })">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-gold focus:px-4 focus:py-2 focus:text-on-gold">{{ __('web.common.skip_to_content') }}</a>

    @if (session('impersonator_id'))
        <div class="relative z-50 flex items-center justify-center gap-3 bg-warning px-4 py-2 text-center text-body-small font-semibold text-[#0B0B0F]">
            <x-ui.icon name="eye" :size="16" />
            <span>{{ __('web.shell.impersonating', ['name' => $user?->name]) }}</span>
            @if (Route::has('impersonate.stop'))
                <form method="POST" action="{{ route('impersonate.stop') }}">@csrf<button class="underline underline-offset-2">{{ __('web.shell.stop_impersonating') }}</button></form>
            @endif
        </div>
    @endif

    <div class="flex min-h-dvh">
        {{-- Mobile backdrop --}}
        <div x-show="mobileNav" x-cloak x-transition.opacity @click="mobileNav = false" class="fixed inset-0 z-40 bg-overlay backdrop-blur-sm lg:hidden"></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 start-0 z-50 flex w-72 flex-col border-e border-border bg-surface transition-[width,transform] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] lg:sticky lg:top-0 lg:h-dvh lg:translate-x-0"
            :class="[collapsed ? 'lg:w-[76px]' : 'lg:w-64', mobileNav ? 'translate-x-0' : 'ltr:-translate-x-full rtl:translate-x-full lg:ltr:translate-x-0 lg:rtl:translate-x-0']"
            aria-label="{{ __('web.shell.primary_nav') }}">
            <div class="flex h-16 items-center justify-between gap-2 px-5" :class="collapsed && 'lg:justify-center lg:px-0'">
                <a href="{{ Route::has('app.dashboard') ? route('app.dashboard') : '/app' }}" class="flex min-w-0 items-center gap-2.5">
                    @if ($tenant?->logo_path)
                        <img src="{{ Storage::disk('public')->url($tenant->logo_path) }}" alt="" class="size-8 shrink-0 rounded-lg object-cover ring-1 ring-border">
                    @else
                        <x-ui.logo :size="30" :wordmark="false" />
                    @endif
                    <span class="truncate text-[15px] font-bold tracking-[-0.02em]" :class="collapsed && 'lg:hidden'">{{ $tenant?->name ?? config('brand.name') }}</span>
                </a>
                <button type="button" class="rounded-lg p-1.5 text-text-muted hover:bg-surface-sunken lg:hidden" @click="mobileNav = false" aria-label="{{ __('web.common.close') }}"><x-ui.icon name="x" :size="18" /></button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 pb-6">
                @foreach ($groups as $group)
                    <p class="mt-6 mb-2 px-3 text-overline font-bold uppercase tracking-[0.12em] text-text-muted" :class="collapsed && 'lg:sr-only'">{{ $group['group'] }}</p>
                    <ul class="space-y-0.5">
                        @foreach ($group['items'] as $item)
                            <li>
                                <a href="{{ $item['href'] }}"
                                    @if ($item['soon']) aria-disabled="true" tabindex="-1" @endif
                                    @if ($item['active']) aria-current="page" @endif
                                    :title="collapsed ? @js($item['label']) : null"
                                    class="group relative flex h-10 items-center gap-3 rounded-xl px-3 text-body-small font-semibold transition duration-150 ease-out
                                        {{ $item['active'] ? 'bg-surface-sunken text-text-primary' : ($item['soon'] ? 'cursor-default text-text-muted/70' : 'text-text-secondary hover:bg-surface-sunken hover:text-text-primary') }}"
                                    :class="collapsed && 'lg:justify-center lg:px-0'">
                                    @if ($item['active'])<span class="absolute inset-y-2 start-0 w-0.5 rounded-full bg-gold" aria-hidden="true"></span>@endif
                                    <x-ui.icon :name="$item['icon']" :size="19" class="{{ $item['active'] ? 'text-gold' : '' }}" />
                                    <span class="flex-1 truncate" :class="collapsed && 'lg:hidden'">{{ $item['label'] }}</span>
                                    @if ($item['soon'])
                                        <span class="rounded-full border border-border px-1.5 py-px text-[10px] font-bold uppercase tracking-[0.08em] text-text-muted" :class="collapsed && 'lg:hidden'">{{ __('web.nav.soon') }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </nav>

            <div class="hidden border-t border-border p-3 lg:block">
                <button type="button" @click="collapsed = !collapsed" class="flex h-10 w-full items-center gap-3 rounded-xl px-3 text-body-small font-semibold text-text-muted hover:bg-surface-sunken hover:text-text-primary" :class="collapsed && 'justify-center px-0'"
                    :aria-label="collapsed ? @js(__('web.shell.expand')) : @js(__('web.shell.collapse'))">
                    <x-ui.icon name="sidebar" :size="18" />
                    <span x-show="!collapsed">{{ __('web.shell.collapse') }}</span>
                </button>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            @if ($trialDays)
                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 border-b border-gold/20 bg-gold/[0.07] px-4 py-2 text-center text-body-small">
                    <span class="text-text-primary">
                        <x-ui.icon name="sparkles" :size="15" class="me-1 inline text-gold" />
                        {{ trans_choice('web.shell.trial_days', $trialDays, ['days' => $trialDays]) }}
                    </span>
                    <a href="{{ route('app.settings.billing') }}" class="font-semibold text-gold underline-offset-4 hover:underline">{{ __('web.shell.trial_cta') }}</a>
                </div>
            @endif

            {{-- Top bar --}}
            <header class="au-glass sticky top-0 z-30 flex h-16 items-center gap-2 border-b border-border px-4 sm:px-6">
                <button type="button" class="rounded-xl p-2 text-text-secondary hover:bg-surface-sunken lg:hidden" @click="mobileNav = true" aria-label="{{ __('web.shell.open_menu') }}"><x-ui.icon name="menu" :size="20" /></button>

                <div x-data="commandPalette(@js($palette))" @keydown.window.meta.k.prevent="toggle()" @keydown.window.ctrl.k.prevent="toggle()" class="flex-1">
                    <button type="button" @click="show()" class="group flex h-10 w-full max-w-md items-center gap-3 rounded-xl border border-border bg-surface px-3.5 text-body-small text-text-muted transition hover:border-border-strong">
                        <x-ui.icon name="search" :size="17" />
                        <span class="flex-1 truncate text-start">{{ __('web.shell.search') }}</span>
                        <kbd class="hidden rounded-md border border-border px-1.5 py-0.5 font-sans text-[11px] font-semibold sm:inline">⌘K</kbd>
                    </button>
                    <template x-teleport="body">
                        <div x-show="open" x-cloak class="fixed inset-0 z-[85] flex items-start justify-center p-4 pt-[12vh]" role="dialog" aria-modal="true" aria-label="{{ __('web.shell.command_palette') }}">
                            <div class="absolute inset-0 bg-overlay backdrop-blur-sm" @click="close()" x-show="open" x-transition.opacity></div>
                            <div x-show="open" x-trap="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 scale-[0.98]" x-transition:enter-end="opacity-100 scale-100"
                                class="relative w-full max-w-xl overflow-hidden rounded-[22px] border border-border bg-surface-raised shadow-e3" @keydown.escape="close()">
                                <div class="flex items-center gap-3 border-b border-border px-4">
                                    <x-ui.icon name="search" :size="18" class="text-text-muted" />
                                    <input x-ref="search" x-model="query" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="go()"
                                        type="text" class="h-14 flex-1 bg-transparent text-body text-text-primary placeholder:text-text-muted focus:outline-none" placeholder="{{ __('web.shell.palette_placeholder') }}" aria-label="{{ __('web.shell.search') }}">
                                    <kbd class="rounded-md border border-border px-1.5 py-0.5 text-[11px] font-semibold text-text-muted">Esc</kbd>
                                </div>
                                <ul class="max-h-80 overflow-y-auto p-2" role="listbox">
                                    <template x-for="(item, i) in results" :key="item.href + item.label">
                                        <li role="option" :aria-selected="i === active">
                                            <a :href="item.href" @mouseenter="active = i" class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-body-small" :class="i === active ? 'bg-surface-sunken text-text-primary' : 'text-text-secondary'">
                                                <span x-text="item.label" class="font-semibold"></span>
                                                <span x-text="item.group" class="text-caption text-text-muted"></span>
                                            </a>
                                        </li>
                                    </template>
                                    <li x-show="results.length === 0" class="px-3 py-8 text-center text-body-small text-text-muted">{{ __('web.shell.palette_empty') }}</li>
                                </ul>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-1">
                    <livewire:app.notifications-bell />
                    <span class="hidden sm:inline-flex">@include('layouts.partials.locale-menu', ['compact' => true])</span>
                    @include('layouts.partials.theme-toggle')
                    <livewire:app.tenant-switcher />
                    <x-ui.dropdown width="w-64" :label="__('web.shell.account')">
                        <x-slot:trigger>
                            <button type="button" class="ms-1 flex items-center rounded-full ring-offset-2 ring-offset-background transition hover:ring-2 hover:ring-gold/40">
                                <x-ui.avatar :name="$user?->name" :src="$user?->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null" size="sm" />
                            </button>
                        </x-slot:trigger>
                        <div class="border-b border-border px-3 pb-3 pt-2">
                            <p class="truncate text-body-small font-semibold text-text-primary">{{ $user?->name }}</p>
                            <p class="truncate text-caption text-text-muted">{{ $user?->email }}</p>
                        </div>
                        <div class="py-1">
                            <x-ui.dropdown-item :href="route('app.settings.profile')" icon="user">{{ __('web.settings.profile') }}</x-ui.dropdown-item>
                            <x-ui.dropdown-item :href="route('app.settings.business')" icon="building">{{ __('web.settings.business') }}</x-ui.dropdown-item>
                            <x-ui.dropdown-item :href="route('app.settings.billing')" icon="receipt">{{ __('web.settings.billing') }}</x-ui.dropdown-item>
                            <x-ui.dropdown-item :href="route('marketing.home', ['locale' => app()->getLocale()])" icon="globe">{{ __('web.shell.website') }}</x-ui.dropdown-item>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-border pt-1">
                            @csrf
                            <x-ui.dropdown-item type="submit" icon="logout">{{ __('web.shell.sign_out') }}</x-ui.dropdown-item>
                        </form>
                    </x-ui.dropdown>
                </div>
            </header>

            <main id="main" class="flex-1 px-4 py-8 sm:px-8 lg:py-10">
                <div class="mx-auto w-full max-w-6xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    <x-ui.toasts />
</body>
</html>
