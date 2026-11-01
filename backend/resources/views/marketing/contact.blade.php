@php $topic = in_array(request('topic'), ['demo', 'sales', 'support', 'migration', 'press', 'partnership'], true) ? request('topic') : 'demo'; @endphp
<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'eyebrow' => __('marketing.contact.eyebrow'), 'title' => __('marketing.contact.title'), 'lead' => __('marketing.contact.lead'), 'actions' => false])
    <section class="au-container grid gap-12 py-20 lg:grid-cols-[1fr_1.3fr]">
        <div class="space-y-4">
            @foreach (['response' => 'clock', 'languages' => 'language', 'email' => 'mail'] as $k => $icon)
                <div class="flex gap-4 rounded-[22px] border border-border bg-surface p-6">
                    <x-ui.icon :name="$icon" :size="22" class="text-gold" />
                    <div>
                        <p class="text-body-small font-semibold text-text-primary">{{ __('marketing.contact.side.'.$k.'.title') }}</p>
                        <p class="mt-1 text-body-small text-text-secondary">{{ __('marketing.contact.side.'.$k.'.text', ['email' => config('brand.support_email')]) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="rounded-[28px] border border-border bg-surface p-7 sm:p-9">
            @if ($sent)
                <div class="flex flex-col items-center py-12 text-center" role="status">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-success-soft text-success"><x-ui.icon name="check" :size="26" :stroke="2.2" /></span>
                    <h2 class="mt-6 text-h3 text-text-primary">{{ __('marketing.contact.sent_title') }}</h2>
                    <p class="mt-2 max-w-sm text-body-small text-text-secondary">{{ __('marketing.contact.sent_text') }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('marketing.contact.submit') }}" class="space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input name="name" :label="__('marketing.contact.name')" required autocomplete="name" maxlength="120" />
                        <x-ui.input name="email" type="email" :label="__('marketing.contact.email')" required autocomplete="email" maxlength="190" />
                    </div>
                    <x-ui.field :label="__('marketing.contact.topic')" for="f-topic">
                        <div class="relative">
                            <select id="f-topic" name="topic" class="block h-12 w-full appearance-none rounded-xl border border-border bg-surface ps-4 pe-10 text-body text-text-primary focus:border-gold focus:outline-none focus:ring-4 focus:ring-gold/15">
                                @foreach (['demo', 'sales', 'support', 'migration', 'press', 'partnership'] as $t)
                                    <option value="{{ $t }}" @selected($t === $topic)>{{ __('marketing.contact.topics.'.$t) }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-text-muted"><x-ui.icon name="chevron-up-down" :size="16" /></span>
                        </div>
                    </x-ui.field>
                    <x-ui.textarea name="message" :label="__('marketing.contact.message')" rows="5" required minlength="10" maxlength="3000" :placeholder="__('marketing.contact.placeholder')" />
                    <div class="hidden" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                    <x-ui.checkbox name="consent" value="1" required :label="__('marketing.contact.consent')" />
                    <x-ui.button type="submit" size="lg" :block="true" icon-end="arrow-right">{{ __('marketing.contact.submit') }}</x-ui.button>
                </form>
            @endif
        </div>
    </section>
</x-layouts::marketing>
