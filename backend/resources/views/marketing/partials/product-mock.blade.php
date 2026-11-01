{{-- Animated product illustration built in HTML/CSS (no images): coach dashboard + AI copilot card. --}}
<div class="relative" role="img" aria-label="{{ __('marketing.mock.aria') }}">
    <div class="pointer-events-none absolute -inset-10 rounded-[48px] bg-[radial-gradient(40rem_20rem_at_60%_30%,rgba(212,175,106,0.22),transparent_70%)] blur-2xl" aria-hidden="true"></div>
    <div class="relative overflow-hidden rounded-[28px] border border-white/10 bg-[#101015] text-[#F5F3EE] shadow-[0_40px_120px_-30px_rgba(0,0,0,0.8)] ring-1 ring-[#D4AF6A]/10" aria-hidden="true">
        {{-- Window chrome --}}
        <div class="flex items-center gap-2 border-b border-white/5 px-4 py-3">
            <span class="size-2.5 rounded-full bg-white/15"></span><span class="size-2.5 rounded-full bg-white/15"></span><span class="size-2.5 rounded-full bg-white/15"></span>
            <div class="mx-auto flex h-6 w-56 items-center justify-center rounded-md bg-white/5 text-[10px] text-white/40">app.{{ config('brand.domain') }}</div>
        </div>
        <div class="grid grid-cols-[52px_1fr] sm:grid-cols-[180px_1fr]">
            {{-- Sidebar --}}
            <div class="border-e border-white/5 p-3">
                <div class="mb-4 flex items-center gap-2"><x-ui.logo :size="22" :wordmark="false" /><span class="hidden text-[12px] font-bold sm:inline">{{ __('marketing.mock.business') }}</span></div>
                @foreach (['home' => 'dashboard', 'users' => 'clients', 'layers' => 'programs', 'leaf' => 'nutrition', 'clipboard-check' => 'checkins', 'chat' => 'messages'] as $icon => $key)
                    <div class="mb-1 flex items-center gap-2 rounded-lg px-2 py-1.5 text-[11px] {{ $loop->first ? 'bg-white/[0.06] text-white' : 'text-white/45' }}">
                        <x-ui.icon :name="$icon" :size="14" class="{{ $loop->first ? 'text-[#D4AF6A]' : '' }}" /><span class="hidden sm:inline">{{ __('web.nav.'.$key) }}</span>
                    </div>
                @endforeach
            </div>
            {{-- Main --}}
            <div class="space-y-3 p-4">
                <div class="flex items-end justify-between">
                    <div><p class="text-[10px] text-white/40">{{ __('marketing.mock.greeting_small') }}</p><p class="text-[15px] font-bold tracking-[-0.02em]">{{ __('marketing.mock.greeting') }}</p></div>
                    <span class="rounded-full border border-[#D4AF6A]/30 bg-[#D4AF6A]/10 px-2 py-0.5 text-[10px] font-semibold text-[#D4AF6A]">{{ __('marketing.mock.live') }}</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([['clients', '48', '+6'], ['checkins', '12', '5'], ['mrr', '$7.2k', '+11%']] as [$k, $v, $d])
                        <div class="rounded-xl border border-white/5 bg-white/[0.03] p-2.5">
                            <p class="text-[9px] text-white/40">{{ __('marketing.mock.kpi_'.$k) }}</p>
                            <p class="mt-1 text-[16px] font-extrabold tracking-[-0.03em] tabular-nums">{{ $v }}</p>
                            <p class="text-[9px] text-[#3DBE8B]">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-5 gap-2">
                    <div class="col-span-3 rounded-xl border border-white/5 bg-white/[0.03] p-3">
                        <p class="text-[9px] text-white/40">{{ __('marketing.mock.compliance') }}</p>
                        <div class="mt-3 flex h-20 items-end gap-1.5">
                            @foreach ([42, 58, 51, 66, 72, 69, 84, 78, 88, 92] as $i => $h)
                                <span class="flex-1 origin-bottom rounded-t-[3px] bg-gradient-to-t from-[#A9853F] to-[#E8CF9B] animate-bar" style="height: {{ $h }}%; animation-delay: {{ $i * 120 }}ms"></span>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-span-2 space-y-1.5 rounded-xl border border-white/5 bg-white/[0.03] p-3">
                        <p class="text-[9px] text-white/40">{{ __('marketing.mock.at_risk') }}</p>
                        @foreach ([['LA', 'red'], ['OK', 'amber'], ['CD', 'amber']] as [$ini, $tone])
                            <div class="flex items-center gap-1.5">
                                <span class="flex size-5 items-center justify-center rounded-full bg-[#D4AF6A]/15 text-[8px] font-bold text-[#D4AF6A]">{{ $ini }}</span>
                                <span class="h-1.5 flex-1 rounded-full bg-white/10"><span class="block h-full rounded-full {{ $tone === 'red' ? 'w-1/4 bg-[#EF5A5A]' : 'w-1/2 bg-[#F2B544]' }}"></span></span>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- AI Copilot card --}}
                <div x-data="{ i: 0, lines: @js(__('marketing.mock.ai_lines')), shown: '', timer: null,
                        type() { const full = this.lines[this.i]; let n = 0; clearInterval(this.timer); this.timer = setInterval(() => { this.shown = full.slice(0, ++n); if (n >= full.length) { clearInterval(this.timer); setTimeout(() => { this.i = (this.i + 1) % this.lines.length; this.type() }, 2600) } }, 22) } }"
                    x-init="window.matchMedia('(prefers-reduced-motion: reduce)').matches ? shown = lines[0] : type()"
                    class="rounded-xl border border-[#D4AF6A]/25 bg-gradient-to-br from-[#D4AF6A]/[0.08] to-transparent p-3">
                    <div class="flex items-center gap-1.5 text-[10px] font-semibold text-[#D4AF6A]"><x-ui.icon name="sparkles" :size="12" />{{ __('marketing.mock.ai_title') }}</div>
                    <p class="mt-1.5 min-h-[2.5rem] text-[11px] leading-relaxed text-white/80"><span x-text="shown"></span><span class="ms-0.5 inline-block h-3 w-px translate-y-0.5 animate-pulse bg-[#D4AF6A]"></span></p>
                    <div class="mt-2 flex gap-1.5">
                        <span class="rounded-md bg-[#D4AF6A] px-2 py-0.5 text-[9px] font-bold text-[#0B0B0F]">{{ __('marketing.mock.approve') }}</span>
                        <span class="rounded-md border border-white/10 px-2 py-0.5 text-[9px] text-white/60">{{ __('marketing.mock.edit') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Floating phone card --}}
    <div class="absolute -bottom-8 -start-4 hidden w-44 animate-float rounded-[22px] border border-white/10 bg-[#16161D] p-3 text-[#F5F3EE] shadow-[0_30px_80px_-20px_rgba(0,0,0,0.9)] sm:block lg:-start-10" aria-hidden="true">
        <p class="text-[9px] text-white/40">{{ __('marketing.mock.phone_small') }}</p>
        <p class="mt-0.5 text-[12px] font-bold">{{ __('marketing.mock.phone_title') }}</p>
        <div class="mt-2 space-y-1.5">
            @foreach ([['80 kg', '8', true], ['85 kg', '6', true], ['85 kg', '6', false]] as [$kg, $reps, $done])
                <div class="flex items-center justify-between rounded-lg bg-white/[0.04] px-2 py-1 text-[10px]">
                    <span class="tabular-nums">{{ $kg }} × {{ $reps }}</span>
                    <span class="flex size-4 items-center justify-center rounded-full {{ $done ? 'bg-[#3DBE8B] text-[#0B0B0F]' : 'border border-white/20' }}">@if ($done)<x-ui.icon name="check" :size="10" :stroke="3" />@endif</span>
                </div>
            @endforeach
        </div>
        <p class="mt-2 text-center text-[9px] font-semibold text-[#D4AF6A]">{{ __('marketing.mock.pr') }}</p>
    </div>
</div>
