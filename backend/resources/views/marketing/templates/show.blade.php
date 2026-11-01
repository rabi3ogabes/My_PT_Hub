<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'eyebrow' => __('marketing.templates.free'), 'title' => $name, 'lead' => $program->translate('description') ?: __('marketing.templates.meta_description'), 'actions' => false])
    <section class="au-container grid items-start gap-12 py-16 lg:grid-cols-[1fr_420px]">
        <div>
            <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ([['weeks', $program->weeks_count ?: '—'], ['days', $program->days_per_week], ['level', __('marketing.exercises.levels.'.$program->level)], ['goal', $program->goal ? __('marketing.templates.goals.'.$program->goal) : '—']] as [$label, $value])
                    <div class="rounded-2xl border border-border bg-surface p-4"><dt class="text-caption text-text-muted">{{ __('marketing.templates.meta.'.$label) }}</dt><dd class="mt-1 text-title font-bold text-text-primary">{{ $value }}</dd></div>
                @endforeach
            </dl>
            <div class="au-prose mt-10">
                <h2>{{ __('marketing.templates.inside_title') }}</h2>
                <ul>@foreach ((array) __('marketing.templates.inside') as $line)<li>{{ $line }}</li>@endforeach</ul>
            </div>
        </div>
        <div id="download" class="rounded-[28px] border border-gold/30 bg-surface p-7 au-glow lg:sticky lg:top-24">
            <h2 class="text-h3 text-text-primary">{{ __('marketing.templates.form_title') }}</h2>
            <p class="mt-2 text-body-small text-text-secondary">{{ __('marketing.templates.form_text') }}</p>
            <form method="POST" action="{{ route('marketing.templates.lead', ['slug' => $program->slug]) }}" class="mt-6 space-y-4">
                <x-ui.input name="email" type="email" :label="__('marketing.contact.email')" required autocomplete="email" icon="mail" />
                @if ($error)<p class="text-caption text-danger" role="alert">{{ __('marketing.templates.invalid_email') }}</p>@endif
                <div class="hidden" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                <x-ui.checkbox name="consent" value="1" :label="__('marketing.templates.consent')" />
                <x-ui.button type="submit" size="lg" :block="true" icon="download">{{ __('marketing.templates.download') }}</x-ui.button>
            </form>
            <p class="mt-4 text-caption text-text-muted">{{ __('marketing.templates.privacy') }}</p>
        </div>
    </section>
</x-layouts::marketing>
