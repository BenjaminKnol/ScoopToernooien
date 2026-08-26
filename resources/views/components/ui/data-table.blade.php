@props(['head' => null])

<div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
    <table {{ $attributes->class('min-w-full text-sm') }}>
        @if($head)
            <thead class="bg-zinc-50 dark:bg-zinc-800">
            <tr class="text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">
                {{ $head }}
            </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
            {{ $slot }}
        </tbody>
    </table>
</div>
