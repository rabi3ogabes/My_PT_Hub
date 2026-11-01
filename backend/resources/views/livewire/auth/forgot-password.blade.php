<div>
    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-caption font-semibold text-text-muted hover:text-text-primary"><x-ui.icon name="arrow-left" :size="14" class="rtl:-scale-x-100" />{{ __('web.auth.back_to_login') }}</a>
    <h1 class="mt-6 text-h1 text-text-primary">{{ __('web.auth.forgot_title') }}</h1>
    <p class="mt-2 text-body text-text-secondary">{{ __('web.auth.forgot_lead') }}</p>
    @if ($sent)
        <div class="mt-8 rounded-[20px] border border-gold/30 bg-gold/[0.06] p-6" role="status">
            <x-ui.icon name="mail" :size="22" class="text-gold" />
            <h2 class="mt-3 text-title text-text-primary">{{ __('web.auth.check_inbox') }}</h2>
            <p class="mt-2 text-body-small text-text-secondary">{{ __('web.auth.forgot_sent', ['email' => $email]) }}</p>
        </div>
    @else
        <form wire:submit="send" class="mt-8 space-y-5">
            <x-ui.input wire:model="email" type="email" :label="__('web.common.email')" autocomplete="email" required autofocus icon="mail" />
            <x-ui.button type="submit" size="lg" :block="true" loading="send">{{ __('web.auth.send_reset') }}</x-ui.button>
        </form>
    @endif
</div>
