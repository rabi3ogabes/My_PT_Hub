<div x-data="{ recovery: false }">
    <span class="flex size-12 items-center justify-center rounded-2xl border border-gold/30 bg-gold/10 text-gold"><x-ui.icon name="shield" :size="22" /></span>
    <h1 class="mt-6 text-h1 text-text-primary">{{ __('web.auth.two_factor_title') }}</h1>
    <p class="mt-2 text-body text-text-secondary" x-show="!recovery">{{ __('web.auth.two_factor_lead') }}</p>
    <p class="mt-2 text-body text-text-secondary" x-show="recovery" x-cloak>{{ __('web.auth.recovery_lead') }}</p>
    <form wire:submit="verify" class="mt-8 space-y-5">
        <x-ui.input wire:model="code" :label="__('web.auth.code')" inputmode="numeric" autocomplete="one-time-code" maxlength="16" required autofocus class="text-center font-mono text-[1.25rem] tracking-[0.3em]" />
        <x-ui.button type="submit" size="lg" :block="true" loading="verify">{{ __('web.auth.verify') }}</x-ui.button>
    </form>
    <button type="button" @click="recovery = !recovery" class="mt-6 text-body-small font-semibold text-text-muted hover:text-text-primary">
        <span x-show="!recovery">{{ __('web.auth.use_recovery') }}</span><span x-show="recovery" x-cloak>{{ __('web.auth.use_app_code') }}</span>
    </button>
</div>
