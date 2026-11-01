<div>
    @include('livewire.app.settings._header', ['title' => __('web.settings.business'), 'lead' => __('web.settings.business_lead'), 'active' => 'business'])
    @unless ($canEdit)
        <div class="mt-8 flex items-center gap-3 rounded-2xl border border-border bg-surface-sunken p-4 text-body-small text-text-secondary"><x-ui.icon name="lock" :size="18" />{{ __('web.settings.owner_only') }}</div>
    @endunless
    <form wire:submit="save">
        <fieldset @disabled(! $canEdit)>
            @component('livewire.app.settings._section', ['title' => __('web.settings.workspace'), 'lead' => __('web.settings.workspace_lead')])
                <div class="space-y-5 rounded-[20px] border border-border bg-surface p-6">
                    <div class="flex items-center gap-5">
                        @if ($logo && method_exists($logo, 'isPreviewable') && $logo->isPreviewable())
                            <img src="{{ $logo->temporaryUrl() }}" alt="" class="size-16 rounded-2xl object-cover ring-1 ring-border">
                        @elseif ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="" class="size-16 rounded-2xl object-cover ring-1 ring-border">
                        @else
                            <span class="flex size-16 items-center justify-center rounded-2xl bg-surface-sunken text-text-muted"><x-ui.icon name="photo" :size="24" /></span>
                        @endif
                        <label class="cursor-pointer"><span class="inline-flex h-9 items-center gap-2 rounded-[10px] border border-border px-3.5 text-[13px] font-semibold text-text-primary hover:border-border-strong"><x-ui.icon name="upload" :size="15" />{{ __('web.onboarding.logo_cta') }}</span><input type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="sr-only"></label>
                        @if ($logoUrl)<x-ui.button size="sm" variant="ghost" wire:click="removeLogo">{{ __('web.common.remove') }}</x-ui.button>@endif
                    </div>
                    @error('logo')<p class="text-caption text-danger">{{ $message }}</p>@enderror
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input wire:model="name" :label="__('web.onboarding.business_name')" required />
                        <x-ui.input :label="__('web.settings.workspace_url')" :value="$slug" disabled id="f-slug" :hint="__('web.settings.workspace_url_hint')" />
                    </div>
                    <x-ui.field :label="__('web.onboarding.brand_color')" :error="$errors->first('brandColor')">
                        <div class="flex flex-wrap items-center gap-3">
                            @foreach ($colors as $c)
                                <button type="button" wire:click="$set('brandColor', '{{ $c }}')" class="size-9 rounded-full ring-offset-2 ring-offset-surface {{ strtoupper($brandColor) === $c ? 'ring-2 ring-text-primary' : '' }}" style="background: {{ $c }}" aria-label="{{ $c }}"></button>
                            @endforeach
                            <input type="color" wire:model.live="brandColor" class="size-9 cursor-pointer rounded-full border-0 bg-transparent p-0" aria-label="{{ __('web.onboarding.custom_color') }}">
                        </div>
                    </x-ui.field>
                </div>
            @endcomponent

            @component('livewire.app.settings._section', ['title' => __('web.settings.region'), 'lead' => __('web.settings.region_lead')])
                <div class="grid gap-5 rounded-[20px] border border-border bg-surface p-6 sm:grid-cols-2">
                    <x-ui.select wire:model="currency" :label="__('web.onboarding.currency')" :options="array_combine($currencies, $currencies)" />
                    <x-ui.select wire:model="locale" :label="__('web.onboarding.language')" :options="collect(\App\View\Locales::all())->mapWithKeys(fn ($l) => [$l => \App\View\Locales::name($l)])->all()" />
                    <x-ui.select wire:model="timezone" :label="__('web.onboarding.timezone')" :options="array_combine($timezones, $timezones)" />
                    <x-ui.input wire:model="country" :label="__('web.settings.country_code')" maxlength="2" :placeholder="'QA'" class="uppercase" :hint="__('web.settings.country_hint')" />
                </div>
            @endcomponent

            @component('livewire.app.settings._section', ['title' => __('web.settings.legal'), 'lead' => __('web.settings.legal_lead')])
                <div class="grid gap-5 rounded-[20px] border border-border bg-surface p-6 sm:grid-cols-2">
                    @foreach (\App\Livewire\App\Settings\Business::LEGAL_FIELDS as $f)
                        @if ($f === 'invoice_footer')
                            <div class="sm:col-span-2"><x-ui.textarea wire:model="legal.{{ $f }}" :label="__('web.settings.legal_fields.'.$f)" rows="3" :optional="true" /></div>
                        @else
                            <x-ui.input wire:model="legal.{{ $f }}" :label="__('web.settings.legal_fields.'.$f)" :type="$f === 'invoice_email' ? 'email' : 'text'" :optional="true" />
                        @endif
                    @endforeach
                </div>
            @endcomponent
        </fieldset>
        @if ($canEdit)
            <div class="sticky bottom-4 mt-4 flex justify-end"><x-ui.button type="submit" loading="save" size="lg" class="shadow-e3">{{ __('web.common.save_changes') }}</x-ui.button></div>
        @endif
    </form>
</div>
