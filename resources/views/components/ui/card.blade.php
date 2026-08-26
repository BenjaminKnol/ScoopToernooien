@props(['title' => null])

<div {{ $attributes->class('rounded-xl border border-zinc-200 bg-white p-4 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900') }}>
    @if($title || isset($actions))
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            @if($title)
                <flux:heading size="lg">{{ $title }}</flux:heading>
            @endif
            @isset($actions)
                <div class="flex items-center gap-3">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</div>
