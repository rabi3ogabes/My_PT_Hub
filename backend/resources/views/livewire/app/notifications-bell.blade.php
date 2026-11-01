<div x-on:au-notification.window="$wire.$refresh()">
    <x-ui.dropdown width="w-80 sm:w-96" :label="__('web.shell.notifications')">
        <x-slot:trigger>
            <button type="button" class="relative inline-flex size-10 items-center justify-center rounded-xl text-text-secondary transition hover:bg-surface-sunken hover:text-text-primary" aria-label="{{ trans_choice('web.shell.notifications_unread', $unread, ['n' => $unread]) }}">
                <x-ui.icon name="bell" :size="19" />
                @if ($unread > 0)<span class="absolute end-2 top-2 flex size-2.5 rounded-full bg-gold ring-2 ring-background" aria-hidden="true"></span>@endif
            </button>
        </x-slot:trigger>
        <div class="flex items-center justify-between px-3 pb-2 pt-1.5" @click.stop>
            <p class="text-body-small font-semibold text-text-primary">{{ __('web.shell.notifications') }}</p>
            @if ($unread > 0)<button type="button" wire:click="markAllRead" class="text-caption font-semibold text-gold hover:underline">{{ __('web.shell.mark_all_read') }}</button>@endif
        </div>
        @forelse ($items as $n)
            <button type="button" wire:click="open('{{ $n->id }}')" class="flex w-full gap-3 rounded-xl px-3 py-2.5 text-start transition hover:bg-surface-sunken">
                <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $n->read_at ? 'bg-transparent' : 'bg-gold' }}" aria-hidden="true"></span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-body-small font-semibold text-text-primary">{{ $n->data['title'] ?? __('web.shell.notification') }}</span>
                    @if (! empty($n->data['body']))<span class="line-clamp-2 block text-caption text-text-secondary">{{ $n->data['body'] }}</span>@endif
                    <span class="mt-0.5 block text-[11px] text-text-muted">{{ $n->created_at?->diffForHumans() }}</span>
                </span>
            </button>
        @empty
            <div class="px-4 py-10 text-center">
                <span class="mx-auto flex size-10 items-center justify-center rounded-xl bg-gold/10 text-gold"><x-ui.icon name="bell" :size="18" /></span>
                <p class="mt-3 text-body-small font-semibold text-text-primary">{{ __('web.shell.notifications_empty') }}</p>
                <p class="mt-1 text-caption text-text-muted">{{ __('web.shell.notifications_empty_text') }}</p>
            </div>
        @endforelse
        <div class="border-t border-border px-3 pt-2" @click.stop>
            <a href="{{ route('app.settings.notifications') }}" class="block py-1 text-caption font-semibold text-text-muted hover:text-text-primary">{{ __('web.settings.notifications') }}</a>
        </div>
    </x-ui.dropdown>
</div>
