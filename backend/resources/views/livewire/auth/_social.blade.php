<div class="grid grid-cols-2 gap-3">
    <a href="{{ route('social.redirect', 'google') }}" class="inline-flex h-12 items-center justify-center gap-2.5 rounded-xl border border-border bg-surface text-body-small font-semibold text-text-primary transition hover:border-border-strong hover:bg-surface-sunken">
        <x-ui.icon name="google" :size="18" />Google
    </a>
    <a href="{{ route('social.redirect', 'apple') }}" class="inline-flex h-12 items-center justify-center gap-2.5 rounded-xl border border-border bg-surface text-body-small font-semibold text-text-primary transition hover:border-border-strong hover:bg-surface-sunken">
        <x-ui.icon name="apple" :size="18" />Apple
    </a>
</div>
