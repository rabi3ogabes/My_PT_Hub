<div>
    @include('livewire.app.settings._header', ['title' => __('web.settings.notifications'), 'lead' => __('web.notifications.lead'), 'active' => 'notifications'])
    <form wire:submit="save">
        @component('livewire.app.settings._section', ['title' => __('web.notifications.channels'), 'lead' => __('web.notifications.channels_lead')])
            <div class="overflow-hidden rounded-[20px] border border-border bg-surface">
                <div class="grid grid-cols-[1fr_72px_72px] items-center border-b border-border px-5 py-3 text-caption font-semibold text-text-muted">
                    <span>{{ __('web.notifications.category') }}</span><span class="text-center">{{ __('web.notifications.push') }}</span><span class="text-center">{{ __('web.notifications.email') }}</span>
                </div>
                <ul class="divide-y divide-border">
                    @foreach ($categories as $c)
                        <li class="grid grid-cols-[1fr_72px_72px] items-center px-5 py-3.5" wire:key="cat-{{ $c }}">
                            <div class="min-w-0 pe-3">
                                <p class="text-body-small font-semibold text-text-primary">{{ __('web.notifications.categories.'.$c.'.title') }}</p>
                                <p class="truncate text-caption text-text-muted">{{ __('web.notifications.categories.'.$c.'.text') }}</p>
                            </div>
                            <div class="flex justify-center"><x-ui.checkbox wire:model="prefs.{{ $c }}.push" id="p-{{ $c }}" :label="''" aria-label="{{ __('web.notifications.push') }} — {{ __('web.notifications.categories.'.$c.'.title') }}" /></div>
                            <div class="flex justify-center">
                                @if (in_array($c, $critical, true))
                                    <span title="{{ __('web.notifications.always') }}" class="text-gold"><x-ui.icon name="lock" :size="16" /><span class="sr-only">{{ __('web.notifications.always') }}</span></span>
                                @else
                                    <x-ui.checkbox wire:model="prefs.{{ $c }}.email" id="e-{{ $c }}" :label="''" aria-label="{{ __('web.notifications.email') }} — {{ __('web.notifications.categories.'.$c.'.title') }}" />
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <p class="mt-3 text-caption text-text-muted">{{ __('web.notifications.critical_note') }}</p>
        @endcomponent

        @component('livewire.app.settings._section', ['title' => __('web.notifications.quiet'), 'lead' => __('web.notifications.quiet_lead')])
            <div class="space-y-5 rounded-[20px] border border-border bg-surface p-6">
                <x-ui.toggle wire:model.live="quietEnabled" :label="__('web.notifications.quiet_toggle')" :description="__('web.notifications.quiet_hint', ['tz' => auth()->user()->timezone ?: 'UTC'])" id="t-quiet" />
                <div class="grid gap-5 sm:grid-cols-2 {{ $quietEnabled ? '' : 'pointer-events-none opacity-50' }}">
                    <x-ui.input wire:model="quietFrom" type="time" :label="__('web.notifications.from')" />
                    <x-ui.input wire:model="quietTo" type="time" :label="__('web.notifications.to')" />
                </div>
            </div>
        @endcomponent
        <div class="flex justify-end"><x-ui.button type="submit" loading="save" size="lg">{{ __('web.common.save_changes') }}</x-ui.button></div>
    </form>
</div>
