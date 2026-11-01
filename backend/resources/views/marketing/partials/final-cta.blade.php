<section class="au-container pt-8">
    <div class="relative overflow-hidden rounded-[32px] border border-gold/25 bg-[#0B0B0F] px-6 py-20 text-center text-[#F5F3EE] sm:px-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50rem_24rem_at_50%_-10%,rgba(212,175,106,0.28),transparent_65%)]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:56px_56px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)]" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-2xl">
            <p class="au-eyebrow">{{ __('marketing.final.eyebrow') }}</p>
            <h2 class="mt-5 text-[clamp(2rem,1.3rem+2.6vw,3.5rem)] leading-[1.04] font-extrabold tracking-[-0.04em]">{{ $title ?? __('marketing.final.title') }}</h2>
            <p class="mt-5 text-[1.0625rem] text-[#B4B1AA]">{{ __('marketing.final.lead') }}</p>
            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                <x-ui.button :href="route('register')" size="lg" icon-end="arrow-right">{{ __('marketing.cta.start_trial') }}</x-ui.button>
                <a href="{{ route('marketing.contact') }}" class="inline-flex h-13 items-center rounded-[14px] border border-white/15 px-7 text-[15px] font-semibold text-[#F5F3EE] transition hover:border-white/30">{{ __('marketing.cta.book_demo') }}</a>
            </div>
            <p class="mt-5 text-caption text-[#7D7A74]">{{ __('marketing.cta.trust') }}</p>
        </div>
    </div>
</section>
