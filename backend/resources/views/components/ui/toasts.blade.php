{{-- Toast stack: session flash (status/success/error) + Livewire `$this->dispatch('toast', type: ..., message: ...)`. --}}
@php
    $flash = collect([
        ['type' => 'success', 'message' => session('status')],
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'error', 'message' => session('error')],
    ])->filter(fn ($t) => filled($t['message']))->values();
@endphp
<div x-data x-init="@js($flash).forEach(t => $store.toasts.push(t))" class="pointer-events-none fixed inset-x-0 bottom-0 z-[90] flex flex-col items-center gap-2 p-4 sm:end-4 sm:start-auto sm:items-end" aria-live="polite" aria-atomic="false">
    <template x-for="t in $store.toasts.items" :key="t.id">
        <div x-transition:enter="transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border border-border bg-surface-raised/95 p-4 shadow-e3 backdrop-blur" role="status">
            <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full"
                :class="{ 'bg-success-soft text-success': t.type === 'success', 'bg-danger-soft text-danger': t.type === 'error', 'bg-info-soft text-info': t.type === 'info', 'bg-warning-soft text-warning': t.type === 'warning' }">
                <svg x-show="t.type === 'success'" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                <svg x-show="t.type !== 'success'" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 8v5M12 16.5h.01"/></svg>
            </span>
            <p class="flex-1 text-body-small font-medium text-text-primary" x-text="t.message"></p>
            <button type="button" class="rounded-md p-0.5 text-text-muted hover:text-text-primary" @click="$store.toasts.dismiss(t.id)" aria-label="{{ __('web.common.dismiss') }}">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
