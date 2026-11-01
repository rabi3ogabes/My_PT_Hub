<button type="button" x-data @click="$store.theme.toggle()" class="inline-flex size-10 items-center justify-center rounded-xl text-text-secondary transition hover:bg-surface-sunken hover:text-text-primary"
    :aria-label="$store.theme.resolved === 'dark' ? @js(__('web.common.theme_light')) : @js(__('web.common.theme_dark'))" aria-label="{{ __('web.common.theme_toggle') }}">
    <x-ui.icon name="sun" :size="18" class="hidden dark:block" />
    <x-ui.icon name="moon" :size="18" class="dark:hidden" />
</button>
