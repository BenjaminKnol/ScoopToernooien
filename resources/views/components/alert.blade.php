@props(['type' => 'success', 'header' => null])

@php
    $palette = $type === 'error'
        ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-400/30 dark:bg-red-400/10 dark:text-red-300'
        : 'border-green-200 bg-green-50 text-green-700 dark:border-green-400/30 dark:bg-green-400/10 dark:text-green-300';
@endphp

@if($slot->isNotEmpty())
    <div x-data="{ show: true }"
         x-show="show"
         x-transition
         x-init="setTimeout(() => show = false, 10000)"
         {{ $attributes->class('fixed top-2 inset-x-0 z-50 mx-auto max-w-3xl rounded-xl border p-4 shadow-lg shadow-black/5 dark:shadow-black/30 ' . $palette) }}
         role="alert"
         style="left: env(safe-area-inset-left); right: env(safe-area-inset-right);">
        <button @click="show = false" type="button" class="absolute top-3 right-3 text-sm font-semibold opacity-70 hover:opacity-100">
            &times;
        </button>
        @if($header)
            <div class="mb-1 pr-6 font-medium">{{ $header }}</div>
        @endif
        <div class="pr-6 text-sm">
            {{ $slot }}
        </div>
        @if(isset($actions))
            <div class="mt-4">
                {{ $actions }}
            </div>
        @endif
    </div>
@endif
