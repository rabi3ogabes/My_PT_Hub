<div>
    @include('livewire.app.settings._header', ['title' => __('web.settings.team'), 'lead' => __('web.team.lead'), 'active' => 'team'])

    <div class="mt-8 flex flex-col gap-4 rounded-[20px] border border-border bg-surface p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <x-ui.progress-ring :value="$limit ? min(100, $used / max(1, $limit) * 100) : 12" :size="52" :stroke="5">{{ $used }}</x-ui.progress-ring>
            <div>
                <p class="text-body-small font-semibold text-text-primary">{{ $limit ? __('web.team.seats', ['used' => $used, 'limit' => $limit]) : __('web.team.seats_unlimited', ['used' => $used]) }}</p>
                <p class="text-caption text-text-muted">{{ $canInvite ? __('web.team.seats_hint') : __('web.team.limit_reached') }}</p>
            </div>
        </div>
        @if ($canManage)
            @if ($canInvite)
                <x-ui.button icon="plus" wire:click="openInvite">{{ __('web.team.invite') }}</x-ui.button>
            @else
                <x-ui.button :href="route('app.settings.billing')" variant="outline-gold" icon="sparkles">{{ __('web.team.upgrade') }}</x-ui.button>
            @endif
        @endif
    </div>

    <div class="mt-6 overflow-hidden rounded-[20px] border border-border bg-surface">
        <ul class="divide-y divide-border">
            @foreach ($members as $m)
                <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center" wire:key="m-{{ $m->user_id }}">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <x-ui.avatar :name="$m->user?->name" :src="$m->user?->avatar_path ? Storage::disk('public')->url($m->user->avatar_path) : null" />
                        <div class="min-w-0">
                            <p class="truncate text-body-small font-semibold text-text-primary">{{ $m->user?->name }} @if ($m->user_id === auth()->id())<span class="text-caption font-normal text-text-muted">· {{ __('web.team.you') }}</span>@endif</p>
                            <p class="truncate text-caption text-text-muted">{{ $m->user?->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($canManage && $m->user_id !== auth()->id())
                            <select wire:change="changeRole('{{ $m->user_id }}', $event.target.value)" class="h-9 rounded-lg border border-border bg-surface px-2 text-caption font-semibold text-text-primary focus:border-gold focus:outline-none" aria-label="{{ __('web.team.role') }}">
                                @foreach (['owner', 'coach', 'assistant'] as $r)<option value="{{ $r }}" @selected($m->role === $r)>{{ __('web.team.roles.'.$r) }}</option>@endforeach
                            </select>
                            <x-ui.button size="sm" variant="ghost" icon="trash" wire:click="confirmArchive('{{ $m->user_id }}')" :aria-label="__('web.team.archive')" />
                        @else
                            <x-ui.badge :tone="$m->role === 'owner' ? 'gold' : 'neutral'">{{ __('web.team.roles.'.$m->role) }}</x-ui.badge>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <h2 class="mt-10 text-title text-text-primary">{{ __('web.team.pending') }}</h2>
    @if ($pending->isEmpty())
        <x-ui.empty-state class="mt-4" :compact="true" icon="user-group" :title="__('web.team.pending_empty')" :description="__('web.team.pending_empty_text')">
            @if ($canManage && $canInvite)<x-ui.button size="sm" variant="secondary" icon="plus" wire:click="openInvite">{{ __('web.team.invite') }}</x-ui.button>@endif
        </x-ui.empty-state>
    @else
        <ul class="mt-4 divide-y divide-border overflow-hidden rounded-[20px] border border-border bg-surface">
            @foreach ($pending as $inv)
                <li class="flex items-center gap-3 p-4" wire:key="i-{{ $inv->id }}">
                    <span class="flex size-10 items-center justify-center rounded-full bg-surface-sunken text-text-muted"><x-ui.icon name="mail" :size="18" /></span>
                    <div class="min-w-0 flex-1"><p class="truncate text-body-small font-semibold text-text-primary">{{ $inv->name ?: $inv->email }}</p><p class="truncate text-caption text-text-muted">{{ $inv->email }} · {{ __('web.team.roles.'.$inv->role) }} · {{ __('web.team.expires', ['when' => $inv->expires_at?->diffForHumans()]) }}</p></div>
                    @if ($canManage)<x-ui.button size="sm" variant="ghost" wire:click="revoke('{{ $inv->id }}')">{{ __('web.team.revoke') }}</x-ui.button>@endif
                </li>
            @endforeach
        </ul>
    @endif

    <x-ui.modal wire:model="inviting" :title="__('web.team.invite_title')" :description="__('web.team.invite_lead')" icon="user-group">
        <form wire:submit="invite" id="team-invite" class="space-y-4">
            <x-ui.input wire:model="inviteName" :label="__('web.common.name')" :optional="true" />
            <x-ui.input wire:model="inviteEmail" type="email" :label="__('web.common.email')" required />
            <x-ui.field :label="__('web.team.role')">
                <div class="grid grid-cols-2 gap-2">
                    @foreach (['coach', 'assistant'] as $r)
                        <label class="cursor-pointer rounded-2xl border p-3 transition {{ $inviteRole === $r ? 'border-gold bg-gold/[0.06]' : 'border-border' }}">
                            <input type="radio" wire:model.live="inviteRole" value="{{ $r }}" class="sr-only">
                            <span class="block text-body-small font-semibold text-text-primary">{{ __('web.team.roles.'.$r) }}</span>
                            <span class="mt-0.5 block text-caption text-text-muted">{{ __('web.team.role_hint.'.$r) }}</span>
                        </label>
                    @endforeach
                </div>
            </x-ui.field>
        </form>
        <x-slot:footer>
            <x-ui.button variant="ghost" @click="open = false">{{ __('web.common.cancel') }}</x-ui.button>
            <x-ui.button type="submit" form="team-invite" loading="invite" icon="mail">{{ __('web.dashboard.send_invite') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <x-ui.modal wire:model="confirmingArchive" :title="__('web.team.archive_title', ['name' => $archiving?->name])" :description="__('web.team.archive_lead')" icon="exclamation" tone="danger">
        <x-slot:footer>
            <x-ui.button variant="ghost" @click="open = false">{{ __('web.common.cancel') }}</x-ui.button>
            <x-ui.button variant="danger" wire:click="archive" loading="archive">{{ __('web.team.archive') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
