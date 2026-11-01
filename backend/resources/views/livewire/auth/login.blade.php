<div>
    <h1 class="text-h1 text-text-primary">{{ __('web.auth.welcome_back') }}</h1>
    <p class="mt-2 text-body text-text-secondary">{{ __('web.auth.login_lead') }}</p>

    @if (session('status'))
        <div class="mt-6 flex items-start gap-3 rounded-2xl border border-success/25 bg-success-soft p-4 text-body-small text-success" role="status"><x-ui.icon name="check-circle" :size="18" />{{ session('status') }}</div>
    @endif

    @if ($magicSent)
        <div class="mt-8 rounded-[20px] border border-gold/30 bg-gold/[0.06] p-6 text-center" role="status">
            <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-gold/15 text-gold"><x-ui.icon name="mail" :size="22" /></span>
            <h2 class="mt-4 text-title text-text-primary">{{ __('web.auth.magic_sent_title') }}</h2>
            <p class="mt-2 text-body-small text-text-secondary">{{ __('web.auth.magic_sent_text', ['email' => $email]) }}</p>
            <button type="button" wire:click="$set('magicSent', false)" class="mt-5 text-body-small font-semibold text-gold hover:underline">{{ __('web.auth.use_password') }}</button>
        </div>
    @else
        <div class="mt-8">@include('livewire.auth._social')</div>
        @include('livewire.auth._divider')
        <form wire:submit="login" class="space-y-5">
            <x-ui.input wire:model="email" type="email" :label="__('web.common.email')" autocomplete="email" required autofocus icon="mail" />
            <div>
                <x-ui.input wire:model="password" type="password" :label="__('web.auth.password')" autocomplete="current-password" />
                <div class="mt-2 flex justify-end"><a href="{{ route('password.request') }}" class="text-caption font-semibold text-text-muted hover:text-text-primary">{{ __('web.auth.forgot') }}</a></div>
            </div>
            <x-ui.checkbox wire:model="remember" :label="__('web.auth.remember')" />
            <x-ui.button type="submit" size="lg" :block="true" loading="login">{{ __('web.auth.sign_in') }}</x-ui.button>
            <x-ui.button size="lg" variant="secondary" :block="true" wire:click="sendMagicLink" loading="sendMagicLink" icon="sparkles">{{ __('web.auth.magic_link') }}</x-ui.button>
        </form>
    @endif

    <p class="mt-8 text-center text-body-small text-text-secondary">{{ __('web.auth.no_account') }} <a href="{{ route('register') }}" class="font-semibold text-gold hover:underline">{{ __('web.auth.start_trial') }}</a></p>
</div>
