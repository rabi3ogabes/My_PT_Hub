{{-- Interactive (Alpine) product demos. $demo = feature slug. --}}
@php $d = 'marketing.demos.'.$demo; @endphp
<div class="relative overflow-hidden rounded-[28px] border border-border bg-surface p-5 shadow-e2 sm:p-7">
    <div class="pointer-events-none absolute inset-0 au-grain opacity-70" aria-hidden="true"></div>
    <div class="relative">
        <div class="mb-5 flex items-center justify-between gap-3">
            <p class="text-label text-text-primary">{{ __($d.'.title') }}</p>
            <x-ui.badge tone="gold" :dot="true">{{ __('marketing.demos.try') }}</x-ui.badge>
        </div>

        @switch($demo)
            @case('workouts')
                <div x-data="{ week: 1, base: 60, step: 2.5, sets: [8, 8, 8] }">
                    <div class="rounded-2xl border border-border bg-surface-raised p-4">
                        <div class="flex items-center justify-between">
                            <div><p class="text-body-small font-semibold text-text-primary">{{ __($d.'.exercise') }}</p><p class="text-caption text-text-muted">{{ __($d.'.rule') }}</p></div>
                            <x-ui.icon name="dumbbell" class="text-gold" />
                        </div>
                        <div class="mt-4 space-y-2">
                            <template x-for="(reps, i) in sets" :key="i">
                                <div class="flex items-center justify-between rounded-xl bg-surface-sunken px-3 py-2 text-body-small">
                                    <span class="text-text-muted">{{ __($d.'.set') }} <span x-text="i + 1"></span></span>
                                    <span class="font-semibold tabular-nums text-text-primary"><span x-text="(base + (week - 1) * step).toFixed(1)"></span> kg × <span x-text="reps"></span></span>
                                </div>
                            </template>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <x-ui.button size="sm" variant="secondary" icon="plus" @click="sets.length < 6 && sets.push(8)">{{ __($d.'.add_set') }}</x-ui.button>
                            <x-ui.button size="sm" variant="ghost" icon="minus" @click="sets.length > 1 && sets.pop()">{{ __($d.'.remove_set') }}</x-ui.button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-caption text-text-muted"><span>{{ __($d.'.week') }} <span x-text="week" class="font-semibold text-text-primary"></span>/8</span><span>{{ __($d.'.auto') }}</span></div>
                        <input type="range" min="1" max="8" x-model.number="week" class="au-range mt-3 w-full" aria-label="{{ __($d.'.week') }}">
                    </div>
                </div>
                @break

            @case('nutrition')
                <div x-data="{ kcal: 2200, get p() { return Math.round(this.kcal * 0.3 / 4) }, get c() { return Math.round(this.kcal * 0.45 / 4) }, get f() { return Math.round(this.kcal * 0.25 / 9) } }">
                    <div class="flex items-end justify-between">
                        <div><p class="text-caption text-text-muted">{{ __($d.'.target') }}</p><p class="text-[2.25rem] font-extrabold tracking-[-0.03em] tabular-nums text-text-primary"><span x-text="kcal"></span> <span class="text-body-small font-semibold text-text-muted">kcal</span></p></div>
                        <x-ui.badge tone="success">{{ __($d.'.ramadan') }}</x-ui.badge>
                    </div>
                    <input type="range" min="1400" max="3600" step="50" x-model.number="kcal" class="au-range mt-4 w-full" aria-label="{{ __($d.'.target') }}">
                    <div class="mt-6 space-y-3">
                        @foreach (['p' => ['protein', 'bg-macro-protein', 30], 'c' => ['carbs', 'bg-macro-carbs', 45], 'f' => ['fat', 'bg-macro-fat', 25]] as $k => [$label, $bg, $pct])
                            <div>
                                <div class="flex justify-between text-caption"><span class="font-semibold text-text-primary">{{ __($d.'.'.$label) }}</span><span class="tabular-nums text-text-muted"><span x-text="{{ $k }}"></span> g</span></div>
                                <div class="mt-1.5 h-2 rounded-full bg-surface-sunken"><div class="h-2 rounded-full {{ $bg }}" style="width: {{ $pct }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @break

            @case('ai-copilot')
                <div x-data="{ state: 'idle' }">
                    <div class="rounded-2xl border border-border bg-surface-raised p-4">
                        <p class="text-caption text-text-muted">{{ __($d.'.context') }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ((array) __($d.'.signals') as $signal)
                                <span class="rounded-full border border-border px-2.5 py-1 text-caption text-text-secondary">{{ $signal }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div x-show="state !== 'idle'" x-transition class="mt-4 rounded-2xl border border-gold/30 bg-gold/[0.06] p-4">
                        <p class="flex items-center gap-1.5 text-caption font-semibold text-gold"><x-ui.icon name="sparkles" :size="14" />{{ __($d.'.draft_label') }}</p>
                        <p class="mt-2 text-body-small leading-relaxed text-text-primary">{{ __($d.'.draft') }}</p>
                        <p class="mt-3 text-caption text-text-muted" x-show="state === 'draft'">{{ __($d.'.approval') }}</p>
                        <p class="mt-3 flex items-center gap-1.5 text-caption font-semibold text-success" x-show="state === 'sent'" x-cloak><x-ui.icon name="check" :size="14" />{{ __($d.'.sent') }}</p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <x-ui.button size="sm" icon="sparkles" x-show="state === 'idle'" @click="state = 'draft'">{{ __($d.'.generate') }}</x-ui.button>
                        <x-ui.button size="sm" icon="check" x-show="state === 'draft'" x-cloak @click="state = 'sent'">{{ __($d.'.approve') }}</x-ui.button>
                        <x-ui.button size="sm" variant="ghost" x-show="state !== 'idle'" x-cloak @click="state = 'idle'">{{ __($d.'.reset') }}</x-ui.button>
                    </div>
                </div>
                @break

            @case('check-ins')
                <div x-data="{ status: 'submitted' }">
                    <div class="rounded-2xl border border-border bg-surface-raised p-4">
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :name="__($d.'.client')" size="sm" />
                            <div class="flex-1"><p class="text-body-small font-semibold text-text-primary">{{ __($d.'.client') }}</p><p class="text-caption text-text-muted">{{ __($d.'.when') }}</p></div>
                            <span x-show="status === 'submitted'"><x-ui.badge tone="warning" :dot="true">{{ __($d.'.to_review') }}</x-ui.badge></span>
                            <span x-show="status === 'reviewed'" x-cloak><x-ui.badge tone="success" :dot="true">{{ __($d.'.reviewed') }}</x-ui.badge></span>
                        </div>
                        <dl class="mt-4 grid grid-cols-3 gap-2 text-center">
                            @foreach ((array) __($d.'.metrics') as [$label, $value])
                                <div class="rounded-xl bg-surface-sunken p-2.5"><dt class="text-[11px] text-text-muted">{{ $label }}</dt><dd class="mt-0.5 text-body-small font-bold tabular-nums text-text-primary">{{ $value }}</dd></div>
                            @endforeach
                        </dl>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <x-ui.button size="sm" icon="check" @click="status = 'reviewed'" x-bind:disabled="status === 'reviewed'">{{ __($d.'.mark') }}</x-ui.button>
                        <x-ui.button size="sm" variant="secondary" icon="chat">{{ __($d.'.voice') }}</x-ui.button>
                    </div>
                </div>
                @break

            @case('payments')
                <div x-data="{ plan: 1, gateway: 'card', plans: @js((array) __($d.'.packages')) }">
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="(p, i) in plans" :key="i">
                            <button type="button" @click="plan = i" class="rounded-2xl border p-3 text-start transition" :class="plan === i ? 'border-gold bg-gold/[0.07]' : 'border-border hover:border-border-strong'">
                                <span class="block text-caption text-text-muted" x-text="p[0]"></span>
                                <span class="mt-1 block text-body-small font-bold text-text-primary" x-text="p[1]"></span>
                            </button>
                        </template>
                    </div>
                    <p class="mt-5 text-caption text-text-muted">{{ __($d.'.pay_with') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['card' => 'Card', 'applepay' => 'Apple Pay', 'mada' => 'mada', 'knet' => 'KNET', 'cmi' => 'CMI'] as $k => $label)
                            <button type="button" @click="gateway = '{{ $k }}'" class="rounded-full border px-3 py-1.5 text-caption font-semibold transition" :class="gateway === '{{ $k }}' ? 'border-gold text-gold' : 'border-border text-text-secondary'">{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="mt-5 flex items-center justify-between rounded-2xl bg-surface-sunken p-4">
                        <span class="text-body-small text-text-secondary">{{ __($d.'.total') }}</span>
                        <span class="text-title font-bold text-text-primary" x-text="plans[plan][1]"></span>
                    </div>
                </div>
                @break

            @case('bookings')
                <div x-data="{ picked: null }">
                    <div class="grid grid-cols-5 gap-2 text-center">
                        @foreach ((array) __($d.'.days') as $di => $day)
                            <div>
                                <p class="text-caption font-semibold text-text-muted">{{ $day }}</p>
                                <div class="mt-2 space-y-1.5">
                                    @foreach (['07:00', '12:30', '18:00'] as $si => $slot)
                                        @php $taken = ($di + $si) % 4 === 0; @endphp
                                        <button type="button" @if (! $taken) @click="picked = '{{ $di }}-{{ $si }}'" @endif {{ $taken ? 'disabled' : '' }}
                                            class="w-full rounded-lg border py-1.5 text-[11px] font-semibold tabular-nums transition {{ $taken ? 'cursor-not-allowed border-border text-text-muted/50 line-through' : '' }}"
                                            @if (! $taken) :class="picked === '{{ $di }}-{{ $si }}' ? 'border-gold bg-gold text-on-gold' : 'border-border text-text-secondary hover:border-gold/50'" @endif>{{ $slot }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-5 flex items-center gap-2 text-caption" :class="picked ? 'text-success' : 'text-text-muted'">
                        <x-ui.icon name="calendar" :size="14" /><span x-text="picked ? @js(__($d.'.booked')) : @js(__($d.'.pick'))"></span>
                    </p>
                </div>
                @break

            @case('community')
                <div x-data="{ likes: 23, liked: false }" class="rounded-2xl border border-border bg-surface-raised p-4">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :name="__($d.'.author')" size="sm" />
                        <div><p class="text-body-small font-semibold text-text-primary">{{ __($d.'.author') }}</p><p class="text-caption text-text-muted">{{ __($d.'.space') }}</p></div>
                    </div>
                    <p class="mt-3 text-body-small text-text-secondary">{{ __($d.'.post') }}</p>
                    <div class="mt-3 flex items-center gap-2">
                        <button type="button" @click="liked = !liked; likes += liked ? 1 : -1" class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-caption font-semibold transition" :class="liked ? 'border-gold text-gold' : 'border-border text-text-secondary'">
                            <x-ui.icon name="fire" :size="14" /><span x-text="likes" class="tabular-nums"></span>
                        </button>
                        <span class="text-caption text-text-muted">{{ __($d.'.challenge') }}</span>
                    </div>
                </div>
                @break

            @case('white-label')
                <div x-data="{ color: '#D4AF6A', name: @js(__($d.'.default_name')) }">
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach (['#D4AF6A', '#3DBE8B', '#5B8DEF', '#E76F6F', '#B07CF2', '#F5F3EE'] as $c)
                            <button type="button" @click="color = '{{ $c }}'" class="size-8 rounded-full ring-offset-2 ring-offset-surface transition" style="background: {{ $c }}" :class="color === '{{ $c }}' && 'ring-2 ring-text-primary'" aria-label="{{ $c }}"></button>
                        @endforeach
                        <input x-model="name" maxlength="18" class="ms-auto h-9 w-40 rounded-lg border border-border bg-surface px-3 text-caption text-text-primary focus:border-gold focus:outline-none" aria-label="{{ __($d.'.name_label') }}">
                    </div>
                    <div class="mx-auto mt-6 w-52 overflow-hidden rounded-[28px] border-4 border-[#1E1E27] bg-[#0B0B0F] text-[#F5F3EE] shadow-e3">
                        <div class="px-4 pb-4 pt-6" :style="`background: linear-gradient(160deg, ${color}33, transparent)`">
                            <p class="text-[10px] text-white/50">{{ __($d.'.welcome') }}</p>
                            <p class="text-[15px] font-bold" x-text="name"></p>
                        </div>
                        <div class="space-y-2 p-3">
                            <div class="rounded-xl p-3 text-[11px] font-bold text-[#0B0B0F]" :style="`background: ${color}`">{{ __($d.'.today') }}</div>
                            <div class="h-8 rounded-xl bg-white/5"></div>
                            <div class="h-8 rounded-xl bg-white/5"></div>
                        </div>
                    </div>
                </div>
                @break

            @default
                <div x-data="{ on: { health: true, calendar: true, zoom: false, whatsapp: false, zapier: false, wearables: true } }" class="space-y-2">
                    @foreach (['health' => 'heart-pulse', 'calendar' => 'calendar', 'zoom' => 'play', 'whatsapp' => 'chat', 'zapier' => 'bolt', 'wearables' => 'clock'] as $k => $icon)
                        <div class="flex items-center justify-between rounded-2xl border border-border bg-surface-raised px-4 py-3">
                            <span class="flex items-center gap-3 text-body-small font-semibold text-text-primary"><x-ui.icon :name="$icon" :size="18" class="text-gold" />{{ __('marketing.demos.integrations.items.'.$k) }}</span>
                            <button type="button" role="switch" :aria-checked="on.{{ $k }}.toString()" @click="on.{{ $k }} = !on.{{ $k }}" class="relative h-6 w-11 rounded-full border transition" :class="on.{{ $k }} ? 'border-gold bg-gold' : 'border-border-strong bg-surface-sunken'" aria-label="{{ __('marketing.demos.integrations.items.'.$k) }}">
                                <span class="absolute top-0.5 size-5 rounded-full shadow transition" :class="on.{{ $k }} ? 'start-[22px] bg-on-gold' : 'start-0.5 bg-text-muted'"></span>
                            </button>
                        </div>
                    @endforeach
                </div>
        @endswitch
    </div>
</div>
