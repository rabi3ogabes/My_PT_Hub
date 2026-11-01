<div>
    <span class="flex size-12 items-center justify-center rounded-2xl border border-gold/30 bg-gold/10 text-gold"><x-ui.icon name="mail" :size="22" /></span>
    <h1 class="mt-6 text-h1 text-text-primary">{{ __('web.auth.verify_title') }}</h1>
    <p class="mt-2 text-body text-text-secondary">{{ __('web.auth.verify_lead', ['email' => auth()->user()?->email]) }}</p>
    @if ($resent)
        <div class="mt-6 flex items-center gap-3 rounded-2xl bg-success-soft p-4 text-body-small text-success" role="status"><x-ui.icon name="check" :size="16" />{{ __('web.auth.verify_resent') }}</div>
    @endif
    <div class="mt-8 flex flex-col gap-3">
        <x-ui.button size="lg" :block="true" wire:click="resend" loading="resend" icon="refresh">{{ __('web.auth.verify_resend') }}</x-ui.button>
        <x-ui.button size="lg" variant="secondary" :block="true" href="/app">{{ __('web.auth.verify_later') }}</x-ui.button>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">@csrf<button type="submit" class="text-body-small font-semibold text-text-muted hover:text-text-primary">{{ __('web.shell.sign_out') }}</button></form>
</div>
