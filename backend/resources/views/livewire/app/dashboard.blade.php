<div class="space-y-10">
    <header class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-body-small text-text-muted">{{ now(auth()->user()->timezone ?: 'UTC')->translatedFormat('l j F') }}</p>
            <h1 class="mt-1 text-h1 text-text-primary">{{ __('web.dashboard.greeting_'.$greeting, ['name' => auth()->user()->firstName()]) }}</h1>
        </div>
        <div class="flex gap-2">
            @if (Route::has('app.calendar.index'))<x-ui.button variant="secondary" icon="calendar" :href="route('app.calendar.index')">{{ __('web.dashboard.schedule') }}</x-ui.button>@endif
            <x-ui.button icon="plus" wire:click="openInvite">{{ __('web.dashboard.invite_client') }}</x-ui.button>
        </div>
    </header>

    @if ($progress < 100)
        <section class="relative overflow-hidden rounded-[24px] border border-gold/25 bg-surface p-6 sm:p-8" aria-labelledby="checklist-title">
            <div class="pointer-events-none absolute inset-0 au-grain" aria-hidden="true"></div>
            <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center">
                <div class="flex items-center gap-5 lg:w-72">
                    <x-ui.progress-ring :value="$progress" :size="72" :label="__('web.dashboard.checklist_title')" />
                    <div>
                        <h2 id="checklist-title" class="text-title text-text-primary">{{ __('web.dashboard.checklist_title') }}</h2>
                        <p class="mt-1 text-caption text-text-muted">{{ __('web.dashboard.checklist_lead') }}</p>
                    </div>
                </div>
                <ol class="grid flex-1 gap-3 sm:grid-cols-2">
                    @foreach ($checklist as $item)
                        <li>
                            @php $clickable = ! $item['done'] && ($item['href'] || $item['action']); @endphp
                            @if ($item['href'] && ! $item['done'])
                                <a href="{{ $item['href'] }}" class="group flex w-full items-center gap-3 rounded-2xl border p-4 text-start transition {{ $item['done'] ? 'border-border bg-surface-sunken/60' : ($clickable ? 'border-border bg-surface-raised hover:border-gold/40' : 'border-border bg-surface-raised opacity-70') }}">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full {{ $item['done'] ? 'bg-gold text-on-gold' : 'border border-border-strong text-transparent' }}"><x-ui.icon name="check" :size="14" :stroke="2.5" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-body-small font-semibold {{ $item['done'] ? 'text-text-muted line-through' : 'text-text-primary' }}">{{ __('web.dashboard.checklist.'.$item['key'].'.title') }}</span>
                                    <span class="block truncate text-caption text-text-muted">{{ __('web.dashboard.checklist.'.$item['key'].'.text') }}</span>
                                </span>
                                @if (! $item['done'])
                                    @if ($clickable)<x-ui.icon name="arrow-right" :size="16" class="text-text-muted transition group-hover:text-gold rtl:-scale-x-100" />@else<span class="rounded-full border border-border px-1.5 py-px text-[10px] font-bold uppercase text-text-muted">{{ __('web.nav.soon') }}</span>@endif
                                @endif
                                </a>
                            @else
                                <button type="button" @if ($item['action'] && ! $item['done']) wire:click="{{ $item['action'] }}" @endif @disabled(! $clickable) class="group flex w-full items-center gap-3 rounded-2xl border p-4 text-start transition {{ $item['done'] ? 'border-border bg-surface-sunken/60' : ($clickable ? 'border-border bg-surface-raised hover:border-gold/40' : 'border-border bg-surface-raised opacity-70') }}">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full {{ $item['done'] ? 'bg-gold text-on-gold' : 'border border-border-strong text-transparent' }}"><x-ui.icon name="check" :size="14" :stroke="2.5" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-body-small font-semibold {{ $item['done'] ? 'text-text-muted line-through' : 'text-text-primary' }}">{{ __('web.dashboard.checklist.'.$item['key'].'.title') }}</span>
                                    <span class="block truncate text-caption text-text-muted">{{ __('web.dashboard.checklist.'.$item['key'].'.text') }}</span>
                                </span>
                                @if (! $item['done'])
                                    @if ($clickable)<x-ui.icon name="arrow-right" :size="16" class="text-text-muted transition group-hover:text-gold rtl:-scale-x-100" />@else<span class="rounded-full border border-border px-1.5 py-px text-[10px] font-bold uppercase text-text-muted">{{ __('web.nav.soon') }}</span>@endif
                                @endif
                                </button>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    <livewire:app.dashboard-kpis />

    <x-ui.modal wire:model="inviting" :title="__('web.dashboard.invite_title')" :description="__('web.dashboard.invite_lead')" icon="user">
        <form wire:submit="invite" class="space-y-4" id="invite-form">
            <x-ui.input wire:model="inviteName" :label="__('web.common.name')" :optional="true" autocomplete="off" />
            <x-ui.input wire:model="inviteEmail" type="email" :label="__('web.common.email')" required autocomplete="off" />
            <p class="text-caption text-text-muted">{{ __('web.dashboard.invite_hint') }}</p>
        </form>
        <x-slot:footer>
            <x-ui.button variant="ghost" @click="open = false">{{ __('web.common.cancel') }}</x-ui.button>
            <x-ui.button type="submit" form="invite-form" loading="invite" icon="mail">{{ __('web.dashboard.send_invite') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
