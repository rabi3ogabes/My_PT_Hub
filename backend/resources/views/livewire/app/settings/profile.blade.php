<div>
    @include('livewire.app.settings._header', ['title' => __('web.settings.profile'), 'lead' => __('web.settings.profile_lead'), 'active' => 'profile'])

    @component('livewire.app.settings._section', ['title' => __('web.settings.identity'), 'lead' => __('web.settings.identity_lead')])
        <form wire:submit="saveProfile" class="space-y-5 rounded-[20px] border border-border bg-surface p-6">
            <div class="flex items-center gap-5">
                @if ($avatar && method_exists($avatar, 'isPreviewable') && $avatar->isPreviewable())
                    <img src="{{ $avatar->temporaryUrl() }}" alt="" class="size-16 rounded-full object-cover ring-1 ring-border">
                @else
                    <x-ui.avatar :name="$name" :src="$avatarUrl" size="lg" />
                @endif
                <label class="cursor-pointer">
                    <span class="inline-flex h-9 items-center gap-2 rounded-[10px] border border-border px-3.5 text-[13px] font-semibold text-text-primary hover:border-border-strong"><x-ui.icon name="upload" :size="15" />{{ __('web.settings.change_avatar') }}</span>
                    <input type="file" wire:model="avatar" accept="image/png,image/jpeg,image/webp" class="sr-only">
                </label>
                <span wire:loading wire:target="avatar" class="text-caption text-gold">{{ __('web.common.uploading') }}</span>
            </div>
            @error('avatar')<p class="text-caption text-danger">{{ $message }}</p>@enderror
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input wire:model="name" :label="__('web.common.name')" required autocomplete="name" />
                <x-ui.input :label="__('web.common.email')" :value="auth()->user()->email" disabled :hint="__('web.settings.email_hint')" id="f-email-readonly" />
                <x-ui.select wire:model="locale" :label="__('web.settings.language')" :options="collect(\App\View\Locales::all())->mapWithKeys(fn ($l) => [$l => \App\View\Locales::name($l)])->all()" />
                <x-ui.select wire:model="units" :label="__('web.settings.units')" :options="['metric' => __('web.settings.metric'), 'imperial' => __('web.settings.imperial')]" />
            </div>
            <x-ui.select wire:model="timezone" :label="__('web.settings.timezone')" :options="array_combine($timezones, $timezones)" />
            <div class="flex justify-end"><x-ui.button type="submit" loading="saveProfile">{{ __('web.common.save') }}</x-ui.button></div>
        </form>
    @endcomponent

    @component('livewire.app.settings._section', ['title' => __('web.settings.public_profile'), 'lead' => __('web.settings.public_profile_lead'), 'id' => 'public-profile'])
        <form wire:submit="savePublicProfile" class="space-y-5 rounded-[20px] border border-border bg-surface p-6">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input wire:model="headline" :label="__('web.settings.headline')" :placeholder="__('web.settings.headline_placeholder')" maxlength="120" />
                <x-ui.input wire:model="city" :label="__('web.settings.city')" maxlength="80" />
            </div>
            <x-ui.textarea wire:model="bio" :label="__('web.settings.bio')" :hint="__('web.settings.bio_hint', ['language' => \App\View\Locales::name(app()->getLocale())])" rows="5" maxlength="3000" />
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-border p-4"><x-ui.toggle wire:model="offersOnline" :label="__('web.settings.offers_online')" id="t-online" /></div>
                <div class="rounded-2xl border border-border p-4"><x-ui.toggle wire:model="offersInPerson" :label="__('web.settings.offers_in_person')" id="t-inperson" /></div>
            </div>
            <div class="rounded-2xl border border-gold/25 bg-gold/[0.05] p-4"><x-ui.toggle wire:model="published" :label="__('web.settings.publish')" :description="__('web.settings.publish_hint')" id="t-publish" /></div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                @if ($profileSlug && $published)
                    <a href="{{ route('marketing.coaches.show', ['locale' => app()->getLocale(), 'slug' => $profileSlug]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-body-small font-semibold text-gold hover:underline">{{ __('web.settings.view_profile') }} <x-ui.icon name="external" :size="14" /></a>
                @else
                    <span></span>
                @endif
                <x-ui.button type="submit" loading="savePublicProfile">{{ __('web.common.save') }}</x-ui.button>
            </div>
        </form>
    @endcomponent

    @component('livewire.app.settings._section', ['title' => __('web.settings.password'), 'lead' => $hasPassword ? __('web.settings.password_lead') : __('web.settings.password_set_lead')])
        <form wire:submit="savePassword" class="space-y-5 rounded-[20px] border border-border bg-surface p-6">
            @if ($hasPassword)<x-ui.input wire:model="current_password" type="password" :label="__('web.settings.current_password')" autocomplete="current-password" />@endif
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input wire:model="password" type="password" :label="__('web.settings.new_password')" autocomplete="new-password" :hint="__('web.auth.password_hint')" />
                <x-ui.input wire:model="password_confirmation" type="password" :label="__('web.settings.confirm_password')" autocomplete="new-password" />
            </div>
            <div class="flex justify-end"><x-ui.button type="submit" loading="savePassword">{{ __('web.settings.update_password') }}</x-ui.button></div>
        </form>
    @endcomponent

    @component('livewire.app.settings._section', ['title' => __('web.settings.two_factor'), 'lead' => __('web.settings.two_factor_lead')])
        <div class="rounded-[20px] border border-border bg-surface p-6">
            @if ($recoveryCodes)
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-success"><x-ui.icon name="shield" :size="22" /><p class="text-body-small font-semibold">{{ __('web.settings.two_factor_on') }}</p></div>
                    <p class="text-body-small text-text-secondary">{{ __('web.settings.recovery_lead') }}</p>
                    <ul class="grid grid-cols-2 gap-2 rounded-2xl bg-surface-sunken p-4 font-mono text-body-small text-text-primary">
                        @foreach ($recoveryCodes as $code)<li>{{ $code }}</li>@endforeach
                    </ul>
                    <div x-data="copy(@js(implode("\n", $recoveryCodes)))"><x-ui.button size="sm" variant="secondary" icon="copy" @click="copy()"><span x-show="!copied">{{ __('web.common.copy') }}</span><span x-show="copied" x-cloak>{{ __('web.common.copied') }}</span></x-ui.button></div>
                </div>
            @elseif ($twoFactorOn)
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-success-soft text-success"><x-ui.icon name="shield" :size="20" /></span><div><p class="text-body-small font-semibold text-text-primary">{{ __('web.settings.two_factor_on') }}</p><p class="text-caption text-text-muted">{{ __('web.settings.two_factor_on_text') }}</p></div></div>
                    <x-ui.button variant="secondary" wire:click="$set('confirmingDisable', true)">{{ __('web.settings.disable') }}</x-ui.button>
                </div>
            @elseif ($twoFactorQr)
                <div class="grid gap-6 sm:grid-cols-[180px_1fr]">
                    <img src="{{ $twoFactorQr }}" alt="{{ __('web.settings.qr_alt') }}" class="size-44 rounded-2xl bg-white p-2">
                    <form wire:submit="confirmTwoFactor" class="space-y-4">
                        <p class="text-body-small text-text-secondary">{{ __('web.settings.scan') }}</p>
                        <p class="break-all rounded-xl bg-surface-sunken px-3 py-2 font-mono text-caption text-text-primary">{{ $twoFactorSecret }}</p>
                        <x-ui.input wire:model="twoFactorCode" :label="__('web.auth.code')" inputmode="numeric" autocomplete="one-time-code" maxlength="6" />
                        <div class="flex gap-2"><x-ui.button type="submit" loading="confirmTwoFactor">{{ __('web.settings.confirm') }}</x-ui.button><x-ui.button variant="ghost" wire:click="cancelTwoFactor">{{ __('web.common.cancel') }}</x-ui.button></div>
                    </form>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-surface-sunken text-text-muted"><x-ui.icon name="lock" :size="20" /></span><p class="text-body-small text-text-secondary">{{ __('web.settings.two_factor_off') }}</p></div>
                    <x-ui.button wire:click="beginTwoFactor" loading="beginTwoFactor" icon="shield">{{ __('web.settings.enable') }}</x-ui.button>
                </div>
            @endif
        </div>
    @endcomponent

    <x-ui.modal wire:model="confirmingDisable" :title="__('web.settings.disable_title')" :description="__('web.settings.disable_lead')" icon="exclamation" tone="danger">
        <form wire:submit="disableTwoFactor" id="disable-2fa">
            @if ($hasPassword)<x-ui.input wire:model="disablePassword" type="password" :label="__('web.settings.current_password')" autocomplete="current-password" />@endif
        </form>
        <x-slot:footer>
            <x-ui.button variant="ghost" @click="open = false">{{ __('web.common.cancel') }}</x-ui.button>
            <x-ui.button variant="danger" type="submit" form="disable-2fa" loading="disableTwoFactor">{{ __('web.settings.disable') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
