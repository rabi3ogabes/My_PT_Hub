<div x-data x-init="try { $wire.set('timezone', Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC', false) } catch (e) {}">
    <x-ui.badge tone="gold" :dot="true">{{ __('web.auth.trial_badge') }}</x-ui.badge>
    <h1 class="mt-4 text-h1 text-text-primary">{{ __('web.auth.register_title') }}</h1>
    <p class="mt-2 text-body text-text-secondary">{{ __('web.auth.register_lead') }}</p>

    <div class="mt-8">@include('livewire.auth._social')</div>
    @include('livewire.auth._divider', ['label' => __('web.auth.or_email')])

    <form wire:submit="register" class="space-y-5">
        <x-ui.input wire:model="name" :label="__('web.auth.full_name')" autocomplete="name" required autofocus />
        <x-ui.input wire:model="email" type="email" :label="__('web.auth.work_email')" autocomplete="email" required icon="mail" />
        <x-ui.input wire:model="password" type="password" :label="__('web.auth.password')" autocomplete="new-password" required :hint="__('web.auth.password_hint')" />
        <x-ui.checkbox wire:model="terms">
            {!! __('web.auth.terms', [
                'terms' => '<a href="'.e(route('marketing.legal', ['locale' => app()->getLocale(), 'page' => 'terms'])).'" target="_blank" class="font-semibold text-text-primary underline underline-offset-2">'.e(__('marketing.legal.terms.title')).'</a>',
                'privacy' => '<a href="'.e(route('marketing.legal', ['locale' => app()->getLocale(), 'page' => 'privacy'])).'" target="_blank" class="font-semibold text-text-primary underline underline-offset-2">'.e(__('marketing.legal.privacy.title')).'</a>',
            ]) !!}
        </x-ui.checkbox>
        <x-ui.button type="submit" size="lg" :block="true" loading="register" icon-end="arrow-right">{{ __('web.auth.start_trial') }}</x-ui.button>
        <p class="flex items-center justify-center gap-2 text-caption font-medium text-text-muted"><x-ui.icon name="shield" :size="14" class="text-gold" />{{ __('web.auth.trust') }}</p>
    </form>

    <p class="mt-8 text-center text-body-small text-text-secondary">{{ __('web.auth.have_account') }} <a href="{{ route('login') }}" class="font-semibold text-gold hover:underline">{{ __('web.auth.sign_in') }}</a></p>
</div>
