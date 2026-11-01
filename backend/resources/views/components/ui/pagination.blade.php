{{-- Paginator view: $paginator->links('components.ui.pagination') --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('web.common.pagination') }}" class="flex items-center justify-between gap-4">
        <p class="text-caption text-text-muted">{{ __('web.common.page_of', ['page' => $paginator->currentPage(), 'pages' => $paginator->lastPage()]) }}</p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex size-10 items-center justify-center rounded-xl border border-border text-text-muted/50" aria-disabled="true"><x-ui.icon name="chevron-left" :size="16" class="rtl:-scale-x-100" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex size-10 items-center justify-center rounded-xl border border-border text-text-secondary hover:border-border-strong hover:text-text-primary" aria-label="{{ __('web.common.previous') }}"><x-ui.icon name="chevron-left" :size="16" class="rtl:-scale-x-100" /></a>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-text-muted">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl bg-gold px-3 text-body-small font-bold text-on-gold tabular-nums">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="hidden h-10 min-w-10 items-center justify-center rounded-xl px-3 text-body-small font-semibold text-text-secondary tabular-nums hover:bg-surface-sunken hover:text-text-primary sm:inline-flex">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex size-10 items-center justify-center rounded-xl border border-border text-text-secondary hover:border-border-strong hover:text-text-primary" aria-label="{{ __('web.common.next') }}"><x-ui.icon name="chevron-right" :size="16" class="rtl:-scale-x-100" /></a>
            @else
                <span class="inline-flex size-10 items-center justify-center rounded-xl border border-border text-text-muted/50" aria-disabled="true"><x-ui.icon name="chevron-right" :size="16" class="rtl:-scale-x-100" /></span>
            @endif
        </div>
    </nav>
@endif
