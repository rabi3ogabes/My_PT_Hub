<div>
    <h1 class="text-h1 text-text-primary">{{ __('web.auth.reset_title') }}</h1>
    <p class="mt-2 text-body text-text-secondary">{{ __('web.auth.reset_lead') }}</p>
    <form wire:submit="resetPassword" class="mt-8 space-y-5">
        <x-ui.input wire:model="email" type="email" :label="__('web.common.email')" autocomplete="email" required icon="mail" />
        <x-ui.input wire:model="password" type="password" :label="__('web.settings.new_password')" autocomplete="new-password" required :hint="__('web.auth.password_hint')" />
        <x-ui.input wire:model="password_confirmation" type="password" :label="__('web.settings.confirm_password')" autocomplete="new-password" required />
        <x-ui.button type="submit" size="lg" :block="true" loading="resetPassword">{{ __('web.auth.reset_cta') }}</x-ui.button>
    </form>
</div>
