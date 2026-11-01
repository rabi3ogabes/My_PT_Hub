<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'icon' => 'book', 'eyebrow' => __('marketing.blog.eyebrow'), 'title' => __('marketing.blog.title'), 'lead' => __('marketing.blog.lead'), 'actions' => false])
    <section class="au-container py-16">
        @if ($posts->isEmpty())
            <x-ui.empty-state icon="book" :title="__('marketing.blog.empty_title')" :description="__('marketing.blog.empty_text')">
                <x-ui.button :href="route('marketing.calculators.index')" variant="secondary">{{ __('marketing.nav.res.calculators') }}</x-ui.button>
                <x-ui.button :href="route('marketing.exercises.index')">{{ __('marketing.nav.res.exercises') }}</x-ui.button>
            </x-ui.empty-state>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <article class="group flex flex-col overflow-hidden rounded-[24px] border border-border bg-surface transition hover:border-border-strong hover:shadow-e2">
                        <a href="{{ route('marketing.blog.show', ['slug' => $post->slug]) }}" class="flex flex-1 flex-col">
                            <div class="relative aspect-[16/9] overflow-hidden bg-[#0B0B0F]">
                                @if ($post->cover_url)<img src="{{ $post->cover_url }}" alt="" width="640" height="360" loading="lazy" decoding="async" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">@else<div class="absolute inset-0 bg-[radial-gradient(30rem_14rem_at_100%_0%,rgba(212,175,106,0.3),transparent_60%)]"></div>@endif
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <p class="text-caption text-text-muted">@if ($post->category){{ __('marketing.blog.categories.'.$post->category) }} · @endif<time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->translatedFormat('j M Y') }}</time></p>
                                <h2 class="mt-3 text-title text-text-primary">{{ $post->title }}</h2>
                                @if ($post->excerpt)<p class="mt-2 line-clamp-3 text-body-small text-text-secondary">{{ $post->excerpt }}</p>@endif
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
            <div class="mt-10">{{ $posts->links('components.ui.pagination') }}</div>
        @endif
    </section>
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
