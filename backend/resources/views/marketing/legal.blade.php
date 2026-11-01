@php $k = 'marketing.legal.'.$page; @endphp
<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'eyebrow' => __('marketing.legal.eyebrow'), 'title' => __($k.'.title'), 'lead' => __($k.'.summary'), 'actions' => false])
    <section class="au-container grid gap-12 py-20 lg:grid-cols-[220px_1fr]">
        <nav class="hidden lg:block" aria-label="{{ __('marketing.legal.eyebrow') }}">
            <ul class="sticky top-28 space-y-1 text-body-small">
                @foreach (\App\View\Marketing\Catalog::LEGAL as $p)
                    <li><a href="{{ route('marketing.legal', ['page' => $p]) }}" class="block rounded-lg px-3 py-2 {{ $p === $page ? 'bg-surface-sunken font-semibold text-text-primary' : 'text-text-secondary hover:text-text-primary' }}" @if ($p === $page) aria-current="page" @endif>{{ __('marketing.legal.'.$p.'.title') }}</a></li>
                @endforeach
            </ul>
        </nav>
        <article class="au-prose max-w-3xl">
            <p class="text-caption text-text-muted">{{ __('marketing.legal.updated', ['date' => \Illuminate\Support\Carbon::parse(__('marketing.legal.date'))->translatedFormat('j F Y')]) }}</p>
            @foreach ((array) __($k.'.sections') as $section)
                <h2>{{ $section['h'] }}</h2>
                @foreach ((array) $section['p'] as $para)<p>{{ str_replace(':brand', config('brand.name'), str_replace(':email', config('brand.support_email'), $para)) }}</p>@endforeach
            @endforeach
        </article>
    </section>
</x-layouts::marketing>
