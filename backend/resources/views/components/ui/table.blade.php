@props(['headers' => []])
<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-[20px] border border-border bg-surface']) }}>
    <table class="w-full text-body-small">
        @if ($headers)
            <thead>
                <tr class="border-b border-border">
                    @foreach ($headers as $h)
                        <th scope="col" class="px-5 py-3 text-start text-caption font-semibold uppercase tracking-[0.06em] text-text-muted">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-border [&_td]:px-5 [&_td]:py-3.5 [&_td]:text-text-secondary">
            {{ $slot }}
        </tbody>
    </table>
</div>
