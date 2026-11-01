@php
    $tones = ['yes' => 'text-success', 'no' => 'text-danger', 'partial' => 'text-warning', 'addon' => 'text-warning', 'varies' => 'text-text-muted'];
    $icons = ['yes' => 'check', 'no' => 'x', 'partial' => 'minus', 'addon' => 'plus', 'varies' => 'info'];
@endphp
<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'eyebrow' => __('marketing.compare.eyebrow'), 'title' => __('marketing.compare.title', ['brand' => config('brand.name'), 'name' => $name]), 'lead' => __('marketing.compare.lead', ['name' => $name])])

    <section class="au-container py-20">
        <div class="overflow-x-auto rounded-[24px] border border-border bg-surface">
            <table class="w-full min-w-[620px] text-body-small">
                <caption class="sr-only">{{ __('marketing.compare.title', ['brand' => config('brand.name'), 'name' => $name]) }}</caption>
                <thead>
                    <tr class="border-b border-border">
                        <th scope="col" class="px-6 py-4 text-start text-caption font-semibold text-text-muted">{{ __('marketing.compare.criteria') }}</th>
                        <th scope="col" class="bg-gold/[0.05] px-6 py-4 text-center font-bold text-gold">{{ config('brand.name') }}</th>
                        <th scope="col" class="px-6 py-4 text-center font-bold text-text-primary">{{ $name }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach (\App\View\Marketing\Catalog::COMPARE_ROWS as $row)
                        @php $v = $matrix[$row] ?? 'varies'; @endphp
                        <tr>
                            <th scope="row" class="px-6 py-4 text-start font-medium text-text-primary">{{ __('marketing.compare.rows.'.$row) }}</th>
                            <td class="bg-gold/[0.05] px-6 py-4 text-center"><span class="inline-flex items-center gap-1.5 font-semibold text-success"><x-ui.icon name="check" :size="16" :stroke="2.2" />{{ __('marketing.compare.values.yes') }}</span></td>
                            <td class="px-6 py-4 text-center"><span class="inline-flex items-center gap-1.5 font-semibold {{ $tones[$v] }}"><x-ui.icon :name="$icons[$v]" :size="16" :stroke="2" />{{ __('marketing.compare.values.'.$v) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-caption leading-relaxed text-text-muted">{{ __('marketing.compare.disclaimer', ['name' => $name, 'date' => \Illuminate\Support\Carbon::parse(\App\View\Marketing\Catalog::COMPARE_REVIEWED)->translatedFormat('F Y')]) }}</p>
    </section>

    <section class="border-y border-border bg-surface/40 py-24">
        <div class="au-container">
            @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.compare.why_eyebrow'), 'title' => __('marketing.compare.why_title', ['name' => $name])])
            <div class="mt-12 grid gap-4 md:grid-cols-3">
                @foreach (['speed', 'local', 'trust'] as $k)
                    <div class="rounded-[24px] border border-border bg-surface p-7">
                        <h3 class="text-title text-text-primary">{{ __('marketing.compare.why.'.$k.'.title') }}</h3>
                        <p class="mt-2 text-body-small leading-relaxed text-text-secondary">{{ __('marketing.compare.why.'.$k.'.text') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="au-container py-24">
        <div class="grid items-center gap-10 rounded-[28px] border border-gold/25 bg-gold/[0.04] p-8 lg:grid-cols-[1fr_auto] lg:p-12">
            <div>
                <p class="au-eyebrow">{{ __('marketing.compare.migrate_eyebrow') }}</p>
                <h2 class="mt-3 text-h2 text-text-primary">{{ __('marketing.compare.migrate_title', ['name' => $name]) }}</h2>
                <p class="mt-3 max-w-2xl text-body text-text-secondary">{{ __('marketing.compare.migrate_text') }}</p>
            </div>
            <x-ui.button :href="route('marketing.switch')" size="lg" icon-end="arrow-right">{{ __('marketing.compare.migrate_cta') }}</x-ui.button>
        </div>
        <div class="mt-10 flex flex-wrap justify-center gap-2">
            @foreach (\App\View\Marketing\Catalog::COMPETITORS as $slug => $other)
                @continue($slug === $competitor)
                <x-ui.chip :href="route('marketing.compare', ['competitor' => $slug])">{{ __('marketing.compare.link', ['name' => $other]) }}</x-ui.chip>
            @endforeach
        </div>
    </section>
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
