<div class="space-y-8" aria-busy="true" aria-label="{{ __('web.common.loading') }}">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
        @for ($i = 0; $i < 6; $i++)
            <div class="rounded-[20px] border border-border bg-surface p-5"><div class="au-skeleton h-3 w-2/3"></div><div class="au-skeleton mt-5 h-8 w-1/2"></div></div>
        @endfor
    </div>
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-[20px] border border-border bg-surface p-6"><x-ui.skeleton :lines="4" :avatar="true" /></div>
        <div class="rounded-[20px] border border-border bg-surface p-6"><x-ui.skeleton :lines="4" /></div>
    </div>
</div>
