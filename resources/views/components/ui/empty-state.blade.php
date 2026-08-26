@props(['message' => null])

<div {{ $attributes->class('text-sm text-zinc-500 dark:text-zinc-400') }}>
    {{ $slot->isEmpty() ? $message : $slot }}
</div>
