<div class="mx-auto max-w-4xl">
    <div class="mb-10 text-center">
        <p class="au-eyebrow">{{ __('web.onboarding.eyebrow') }}</p>
        <h1 class="mt-3 text-h1 text-text-primary">{{ $step === 4 ? __('web.onboarding.done_title') : __('web.onboarding.title') }}</h1>
        <p class="mx-auto mt-3 max-w-lg text-body text-text-secondary">{{ $step === 4 ? __('web.onboarding.done_lead') : __('web.onboarding.lead') }}</p>
    </div>

    {{-- Stepper --}}
    <ol class="mx-auto mb-10 flex max-w-xl items-center gap-2" aria-label="{{ __('web.onboarding.progress') }}">
        @foreach (['business', 'brand', 'region', 'invite'] as $i => $s)
            @php $n = $i + 1; @endphp
            <li class="flex flex-1 items-center gap-2" @if ($n === $step) aria-current="step" @endif>
                <span class="flex size-8 shrink-0 items-center justify-center rounded-full text-caption font-bold transition {{ $n < $step ? 'bg-gold text-on-gold' : ($n === $step ? 'border-2 border-gold text-gold' : 'border border-border text-text-muted') }}">
                    @if ($n < $step)<x-ui.icon name="check" :size="14" :stroke="2.5" />@else{{ $n }}@endif
                </span>
                <span class="hidden text-caption font-semibold sm:inline {{ $n <= $step ? 'text-text-primary' : 'text-text-muted' }}">{{ __('web.onboarding.steps.'.$s) }}</span>
                @if (! $loop->last)<span class="h-px flex-1 {{ $n < $step ? 'bg-gold' : 'bg-border' }}"></span>@endif
            </li>
        @endforeach
    </ol>

    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="rounded-[24px] border border-border bg-surface p-6 sm:p-8">
            @if ($step === 1)
                <form wire:submit="next" class="space-y-6">
                    <x-ui.input wire:model.live.debounce.300ms="name" :label="__('web.onboarding.business_name')" :hint="__('web.onboarding.business_hint')" required maxlength="120" autofocus />
                    <x-ui.field :label="__('web.onboarding.logo')" :hint="__('web.onboarding.logo_hint')" :error="$errors->first('logo')" :optional="true">
                        <label class="flex cursor-pointer items-center gap-4 rounded-2xl border border-dashed border-border-strong p-4 transition hover:border-gold/50">
                            @if ($logo && method_exists($logo, 'isPreviewable') && $logo->isPreviewable())
                                <img src="{{ $logo->temporaryUrl() }}" alt="" class="size-14 rounded-xl object-cover">
                            @elseif ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="" class="size-14 rounded-xl object-cover">
                            @else
                                <span class="flex size-14 items-center justify-center rounded-xl bg-surface-sunken text-text-muted"><x-ui.icon name="photo" :size="22" /></span>
                            @endif
                            <span class="flex-1 text-body-small text-text-secondary">
                                <span class="font-semibold text-text-primary">{{ __('web.onboarding.logo_cta') }}</span><br>{{ __('web.onboarding.logo_types') }}
                            </span>
                            <span wire:loading wire:target="logo" class="text-caption text-gold">{{ __('web.common.uploading') }}</span>
                            <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="sr-only">
                        </label>
                    </x-ui.field>
                    <div class="flex justify-end"><x-ui.button type="submit" loading="next" icon-end="arrow-right">{{ __('web.common.continue') }}</x-ui.button></div>
                </form>
            @elseif ($step === 2)
                <form wire:submit="next" class="space-y-6">
                    <x-ui.field :label="__('web.onboarding.brand_color')" :hint="__('web.onboarding.brand_hint')" :error="$errors->first('brandColor')">
                        <div class="flex flex-wrap items-center gap-3">
                            @foreach ($colors as $c)
                                <button type="button" wire:click="$set('brandColor', '{{ $c }}')" class="size-10 rounded-full ring-offset-2 ring-offset-surface transition hover:scale-105 {{ strtoupper($brandColor) === $c ? 'ring-2 ring-text-primary' : '' }}" style="background: {{ $c }}" aria-label="{{ $c }}" aria-pressed="{{ strtoupper($brandColor) === $c ? 'true' : 'false' }}"></button>
                            @endforeach
                            <label class="flex h-10 items-center gap-2 rounded-full border border-border ps-1 pe-3">
                                <input type="color" wire:model.live="brandColor" class="size-8 cursor-pointer rounded-full border-0 bg-transparent p-0" aria-label="{{ __('web.onboarding.custom_color') }}">
                                <span class="font-mono text-caption uppercase text-text-secondary">{{ $brandColor }}</span>
                            </label>
                        </div>
                    </x-ui.field>
                    <div class="flex justify-between"><x-ui.button variant="ghost" wire:click="back" icon="arrow-left">{{ __('web.common.back') }}</x-ui.button><x-ui.button type="submit" icon-end="arrow-right">{{ __('web.common.continue') }}</x-ui.button></div>
                </form>
            @elseif ($step === 3)
                <form wire:submit="next" class="space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.select wire:model="currency" :label="__('web.onboarding.currency')" :options="array_combine($currencies, $currencies)" />
                        <x-ui.select wire:model="locale" :label="__('web.onboarding.language')" :options="collect(\App\View\Locales::all())->mapWithKeys(fn ($l) => [$l => \App\View\Locales::name($l)])->all()" />
                    </div>
                    <x-ui.select wire:model="timezone" :label="__('web.onboarding.timezone')" :hint="__('web.onboarding.timezone_hint')" :options="array_combine($timezones, $timezones)" />
                    <div class="flex justify-between"><x-ui.button variant="ghost" wire:click="back" icon="arrow-left">{{ __('web.common.back') }}</x-ui.button><x-ui.button type="submit" loading="next" icon="check">{{ __('web.onboarding.finish') }}</x-ui.button></div>
                </form>
            @else
                <div class="space-y-6">
                    <div class="flex items-center gap-4 rounded-2xl bg-success-soft p-4 text-success"><x-ui.icon name="check-circle" :size="22" /><p class="text-body-small font-semibold">{{ __('web.onboarding.saved') }}</p></div>
                    <div>
                        <h2 class="text-title text-text-primary">{{ __('web.onboarding.invite_title') }}</h2>
                        <p class="mt-1 text-body-small text-text-secondary">{{ __('web.onboarding.invite_lead') }}</p>
                    </div>
                    @if ($invited)
                        <x-ui.empty-state :compact="true" icon="mail" :title="__('web.onboarding.invited_title')" :description="__('web.onboarding.invited_text')">
                            <x-ui.button :href="route('app.dashboard')" icon-end="arrow-right">{{ __('web.onboarding.to_dashboard') }}</x-ui.button>
                        </x-ui.empty-state>
                    @else
                        <form wire:submit="invite" class="flex flex-col gap-3 sm:flex-row sm:items-start">
                            <div class="flex-1"><x-ui.input wire:model="inviteEmail" type="email" :placeholder="__('web.onboarding.invite_placeholder')" icon="mail" :aria-label="__('web.common.email')" /></div>
                            <x-ui.button type="submit" loading="invite" class="h-12">{{ __('web.dashboard.send_invite') }}</x-ui.button>
                        </form>
                        <a href="{{ route('app.dashboard') }}" class="inline-flex items-center gap-1.5 text-body-small font-semibold text-text-muted hover:text-text-primary">{{ __('web.onboarding.skip') }} <x-ui.icon name="arrow-right" :size="14" class="rtl:-scale-x-100" /></a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Live brand preview --}}
        <aside class="hidden lg:block" aria-label="{{ __('web.onboarding.preview') }}">
            <p class="mb-3 text-caption font-semibold text-text-muted">{{ __('web.onboarding.preview') }}</p>
            <div class="mx-auto w-64 overflow-hidden rounded-[36px] border-[6px] border-[#1E1E27] bg-[#0B0B0F] text-[#F5F3EE] shadow-e3">
                <div class="px-5 pb-5 pt-8" style="background: linear-gradient(160deg, {{ preg_match('/^#[0-9A-Fa-f]{6}$/', $brandColor) ? $brandColor : '#D4AF6A' }}40, transparent)">
                    <div class="flex items-center gap-2">
                        @if ($logo && method_exists($logo, 'isPreviewable') && $logo->isPreviewable())<img src="{{ $logo->temporaryUrl() }}" alt="" class="size-7 rounded-lg object-cover">@elseif ($logoUrl)<img src="{{ $logoUrl }}" alt="" class="size-7 rounded-lg object-cover">@else<x-ui.logo :size="26" :wordmark="false" />@endif
                        <span class="truncate text-[13px] font-bold">{{ $name ?: __('web.onboarding.business_name') }}</span>
                    </div>
                    <p class="mt-6 text-[11px] text-white/50">{{ __('web.onboarding.preview_hi') }}</p>
                    <p class="text-[17px] font-bold">{{ __('web.onboarding.preview_today') }}</p>
                </div>
                <div class="space-y-2 p-4">
                    <div class="rounded-2xl p-4 text-[12px] font-bold text-[#0B0B0F]" style="background: {{ preg_match('/^#[0-9A-Fa-f]{6}$/', $brandColor) ? $brandColor : '#D4AF6A' }}">{{ __('web.onboarding.preview_workout') }}</div>
                    <div class="h-10 rounded-2xl bg-white/5"></div>
                    <div class="h-10 rounded-2xl bg-white/5"></div>
                </div>
            </div>
        </aside>
    </div>
</div>
