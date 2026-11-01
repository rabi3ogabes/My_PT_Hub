@php
    $color = $tenant && preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $tenant->brand_color) ? $tenant->brand_color : '#D4AF6A';
    $isClient = ($invitation?->role ?? 'client') === 'client';
    $inviteUrl = route('invite.show', $code);
    $qr = (new \chillerlan\QRCode\QRCode(new \chillerlan\QRCode\QROptions(['outputBase64' => true])))->render($inviteUrl);
@endphp
<div>
    @if ($error)
        <span class="flex size-12 items-center justify-center rounded-2xl bg-danger-soft text-danger"><x-ui.icon name="exclamation" :size="22" /></span>
        <h1 class="mt-6 text-h1 text-text-primary">{{ __('web.invite.invalid_title') }}</h1>
        <p class="mt-2 text-body text-text-secondary">{{ $error }}</p>
        <p class="mt-2 text-body-small text-text-muted">{{ __('web.invite.invalid_hint') }}</p>
        <x-ui.button class="mt-8" :href="route('login')" variant="secondary">{{ __('web.auth.sign_in') }}</x-ui.button>
    @else
        {{-- Tenant branding --}}
        <div class="relative overflow-hidden rounded-[24px] border border-border p-6" style="background: linear-gradient(150deg, {{ $color }}26, transparent 70%)">
            <div class="flex items-center gap-4">
                @if ($tenant?->logo_path)
                    <img src="{{ Storage::disk('public')->url($tenant->logo_path) }}" alt="" class="size-14 rounded-2xl object-cover ring-1 ring-border">
                @else
                    <span class="flex size-14 items-center justify-center rounded-2xl text-h3 font-extrabold text-[#0B0B0F]" style="background: {{ $color }}">{{ mb_strtoupper(mb_substr((string) $tenant?->name, 0, 1)) }}</span>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-title text-text-primary">{{ $tenant?->name }}</p>
                    @if ($invitation?->inviter)<p class="text-caption text-text-secondary">{{ __('web.invite.from', ['coach' => $invitation->inviter->name]) }}</p>@endif
                </div>
            </div>
        </div>

        <h1 class="mt-8 text-h1 text-text-primary">{{ $isClient ? __('web.invite.client_title', ['coach' => $invitation?->inviter?->firstName() ?? $tenant?->name]) : __('web.invite.staff_title', ['business' => $tenant?->name]) }}</h1>
        <p class="mt-2 text-body text-text-secondary">{{ $isClient ? __('web.invite.client_lead') : __('web.invite.staff_lead', ['role' => __('web.team.roles.'.$invitation?->role)]) }}</p>

        @if ($done)
            <div class="mt-8 rounded-[20px] border border-success/25 bg-success-soft p-6" role="status">
                <p class="flex items-center gap-2 text-body-small font-semibold text-success"><x-ui.icon name="check-circle" :size="18" />{{ __('web.invite.done_title') }}</p>
                <p class="mt-2 text-body-small text-text-secondary">{{ __('web.invite.done_text') }}</p>
            </div>
        @endif

        @if ($isClient)
            <div class="mt-8 grid gap-5 rounded-[20px] border border-border bg-surface p-5 sm:grid-cols-[1fr_auto] sm:items-center">
                <div>
                    <p class="text-body-small font-semibold text-text-primary">{{ __('web.invite.get_app') }}</p>
                    <p class="mt-1 text-caption text-text-muted">{{ __('web.invite.code_hint') }}</p>
                    <div x-data="copy(@js($code))" class="mt-3 flex items-center gap-2">
                        <span class="rounded-xl border border-gold/30 bg-gold/[0.06] px-3 py-1.5 font-mono text-body font-bold tracking-[0.18em] text-gold">{{ $code }}</span>
                        <button type="button" @click="copy()" class="rounded-lg p-2 text-text-muted hover:bg-surface-sunken hover:text-text-primary" aria-label="{{ __('web.common.copy') }}"><x-ui.icon name="copy" :size="16" x-show="!copied" /><x-ui.icon name="check" :size="16" x-show="copied" x-cloak class="text-success" /></button>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">@include('marketing.partials.store-badges', ['compact' => true])</div>
                    <a href="{{ $inviteUrl }}" class="mt-3 inline-flex items-center gap-1.5 text-caption font-semibold text-gold sm:hidden">{{ __('web.invite.open_app') }} <x-ui.icon name="arrow-right" :size="13" class="rtl:-scale-x-100" /></a>
                </div>
                <figure class="hidden text-center sm:block">
                    <img src="{{ $qr }}" alt="{{ __('web.invite.qr_alt') }}" class="size-28 rounded-xl bg-white p-1.5">
                    <figcaption class="mt-1.5 text-[11px] text-text-muted">{{ __('web.invite.scan') }}</figcaption>
                </figure>
            </div>
        @endif

        @unless ($done)
            @auth
                <div class="mt-8 rounded-[20px] border border-border bg-surface p-5">
                    <p class="text-body-small text-text-secondary">{{ __('web.invite.signed_in_as', ['email' => auth()->user()->email]) }}</p>
                    <x-ui.button class="mt-4" :block="true" size="lg" wire:click="accept" loading="accept">{{ __('web.invite.accept') }}</x-ui.button>
                    @error('email')<p class="mt-3 text-caption text-danger" role="alert">{{ $message }}</p>@enderror
                </div>
            @else
                @include('livewire.auth._divider', ['label' => $isClient ? __('web.invite.or_web') : __('web.invite.create_account')])
                <form wire:submit="accept" class="space-y-5">
                    <x-ui.input wire:model="name" :label="__('web.auth.full_name')" autocomplete="name" required />
                    <x-ui.input wire:model="email" type="email" :label="__('web.common.email')" autocomplete="email" required icon="mail" />
                    <x-ui.input wire:model="password" type="password" :label="__('web.auth.password')" autocomplete="new-password" required :hint="__('web.auth.password_hint')" />
                    <x-ui.checkbox wire:model="terms">
                        {!! __('web.auth.terms', [
                            'terms' => '<a href="'.e(route('marketing.legal', ['locale' => app()->getLocale(), 'page' => 'terms'])).'" target="_blank" class="font-semibold text-text-primary underline underline-offset-2">'.e(__('marketing.legal.terms.title')).'</a>',
                            'privacy' => '<a href="'.e(route('marketing.legal', ['locale' => app()->getLocale(), 'page' => 'privacy'])).'" target="_blank" class="font-semibold text-text-primary underline underline-offset-2">'.e(__('marketing.legal.privacy.title')).'</a>',
                        ]) !!}
                    </x-ui.checkbox>
                    <x-ui.button type="submit" size="lg" :block="true" loading="accept">{{ $isClient ? __('web.invite.create_web') : __('web.invite.join', ['business' => $tenant?->name]) }}</x-ui.button>
                </form>
                <p class="mt-6 text-center text-body-small text-text-secondary">{{ __('web.auth.have_account') }} <a href="{{ route('login') }}" class="font-semibold text-gold hover:underline">{{ __('web.auth.sign_in') }}</a></p>
            @endauth
        @endunless
    @endif
</div>
