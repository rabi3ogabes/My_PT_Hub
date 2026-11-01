<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'icon' => 'refresh', 'eyebrow' => __('marketing.switch.eyebrow'), 'title' => __('marketing.switch.title'), 'lead' => __('marketing.switch.lead')])

    <section class="au-container py-24">
        <ol class="grid gap-4 md:grid-cols-3">
            @foreach ((array) __('marketing.switch.steps') as $i => $step)
                <li class="relative rounded-[24px] border border-border bg-surface p-7">
                    <span class="text-[2.5rem] font-extrabold tracking-[-0.04em] text-gold/40 tabular-nums">0{{ $i + 1 }}</span>
                    <h2 class="mt-3 text-title text-text-primary">{{ $step['t'] }}</h2>
                    <p class="mt-2 text-body-small leading-relaxed text-text-secondary">{{ $step['d'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="border-y border-border bg-surface/40 py-24">
        <div class="au-container grid gap-10 lg:grid-cols-2">
            <div class="rounded-[28px] border border-border bg-surface p-8">
                <x-ui.icon name="upload" :size="24" class="text-gold" />
                <h2 class="mt-5 text-h3 text-text-primary">{{ __('marketing.switch.self.title') }}</h2>
                <p class="mt-3 text-body-small text-text-secondary">{{ __('marketing.switch.self.text') }}</p>
                <ul class="mt-6 space-y-2.5">
                    @foreach ((array) __('marketing.switch.self.items') as $item)
                        <li class="flex gap-3 text-body-small text-text-secondary"><x-ui.icon name="check" :size="16" class="mt-0.5 text-gold" />{{ $item }}</li>
                    @endforeach
                </ul>
                <x-ui.button :href="route('register')" class="mt-8" icon-end="arrow-right">{{ __('marketing.cta.start_trial') }}</x-ui.button>
            </div>
            <div class="rounded-[28px] border border-gold/40 bg-surface p-8 au-glow">
                <x-ui.icon name="sparkles" :size="24" class="text-gold" />
                <h2 class="mt-5 text-h3 text-text-primary">{{ __('marketing.switch.done.title') }}</h2>
                <p class="mt-3 text-body-small text-text-secondary">{{ __('marketing.switch.done.text') }}</p>
                <ul class="mt-6 space-y-2.5">
                    @foreach ((array) __('marketing.switch.done.items') as $item)
                        <li class="flex gap-3 text-body-small text-text-secondary"><x-ui.icon name="check" :size="16" class="mt-0.5 text-gold" />{{ $item }}</li>
                    @endforeach
                </ul>
                <x-ui.button :href="route('marketing.contact', ['topic' => 'migration'])" variant="secondary" class="mt-8" icon-end="arrow-right">{{ __('marketing.switch.done.cta') }}</x-ui.button>
            </div>
        </div>
    </section>

    @include('marketing.partials.faq', ['faq' => $faq])
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
