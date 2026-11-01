{{-- $faq: list of [question, answer] --}}
@if (! empty($faq))
    <section class="au-container py-24">
        @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.faq.eyebrow'), 'title' => $title ?? __('marketing.faq.title')])
        <div class="mx-auto mt-12 max-w-3xl divide-y divide-border rounded-[24px] border border-border bg-surface">
            @foreach ($faq as $i => [$q, $a])
                <div x-data="{ open: {{ $i === 0 ? 'true' : 'false' }} }">
                    <h3>
                        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="flex w-full items-center justify-between gap-6 px-6 py-5 text-start text-[15px] font-semibold text-text-primary">
                            <span>{{ $q }}</span>
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full border border-border text-text-muted transition duration-200" :class="open && 'rotate-45 border-gold/40 text-gold'"><x-ui.icon name="plus" :size="14" /></span>
                        </button>
                    </h3>
                    <div x-show="open" x-collapse>
                        <p class="px-6 pb-6 text-body-small leading-relaxed text-text-secondary">{{ $a }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
