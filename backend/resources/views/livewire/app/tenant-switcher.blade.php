<div>
    @if ($tenants->count() > 1)
        <x-ui.dropdown width="w-64" :label="__('web.shell.switch_workspace')">
            <x-slot:trigger>
                <button type="button" class="hidden h-10 items-center gap-2 rounded-xl border border-border px-2.5 text-body-small font-semibold text-text-secondary transition hover:border-border-strong hover:text-text-primary md:inline-flex">
                    <x-ui.icon name="building" :size="16" />
                    <span class="max-w-[9rem] truncate">{{ $tenants->firstWhere('id', $current)?->name }}</span>
                    <x-ui.icon name="chevron-up-down" :size="14" />
                </button>
            </x-slot:trigger>
            <p class="px-3 pb-1.5 pt-1 text-caption font-semibold text-text-muted">{{ __('web.shell.switch_workspace') }}</p>
            @foreach ($tenants as $t)
                <x-ui.dropdown-item wire:click="switchTo('{{ $t->id }}')" :active="$t->id === $current">
                    <span class="flex items-center gap-2"><span class="size-2.5 rounded-full" style="background: {{ preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $t->brand_color) ? $t->brand_color : '#D4AF6A' }}"></span>{{ $t->name }}<span class="text-caption text-text-muted">· {{ __('web.team.roles.'.$roles[$t->id]) }}</span></span>
                </x-ui.dropdown-item>
            @endforeach
        </x-ui.dropdown>
    @endif
</div>
