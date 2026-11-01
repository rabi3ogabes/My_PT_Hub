<x-layouts::marketing :seo="$seo">
    <article>
        <header class="relative overflow-hidden border-b border-border">
            <div class="pointer-events-none absolute inset-0 au-grain" aria-hidden="true"></div>
            <div class="au-container relative max-w-3xl py-16 lg:py-24">
                <a href="{{ route('marketing.blog.index') }}" class="inline-flex items-center gap-1.5 text-caption font-semibold text-text-muted hover:text-text-primary"><x-ui.icon name="arrow-left" :size="14" class="rtl:-scale-x-100" />{{ __('marketing.nav.res.blog') }}</a>
                <p class="mt-8 text-caption text-text-muted">@if ($post->category){{ __('marketing.blog.categories.'.$post->category) }} · @endif<time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->translatedFormat('j F Y') }}</time> · {{ trans_choice('marketing.blog.minutes', $minutes, ['n' => $minutes]) }}</p>
                <h1 class="mt-4 text-[clamp(2.25rem,1.6rem+2.6vw,3.75rem)] leading-[1.05] font-extrabold tracking-[-0.04em] text-text-primary">{{ $post->title }}</h1>
                @if ($post->excerpt)<p class="mt-6 text-[1.1875rem] leading-relaxed text-text-secondary">{{ $post->excerpt }}</p>@endif
            </div>
        </header>
        @if ($post->cover_url)
            <div class="au-container max-w-4xl pt-12"><img src="{{ $post->cover_url }}" alt="" width="1200" height="630" class="w-full rounded-[24px] border border-border object-cover" fetchpriority="high"></div>
        @endif
        <div class="au-container max-w-3xl py-14">
            <div class="au-prose">{!! $html !!}</div>
        </div>
    </article>
    @if ($related->isNotEmpty())
        <section class="au-container max-w-5xl pb-8">
            <h2 class="text-h3 text-text-primary">{{ __('marketing.blog.related') }}</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                @foreach ($related as $r)
                    <a href="{{ route('marketing.blog.show', ['slug' => $r->slug]) }}" class="rounded-[20px] border border-border bg-surface p-5 transition hover:border-border-strong"><p class="text-body-small font-semibold text-text-primary">{{ $r->title }}</p><p class="mt-2 text-caption text-text-muted">{{ $r->published_at?->translatedFormat('j M Y') }}</p></a>
                @endforeach
            </div>
        </section>
    @endif
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
