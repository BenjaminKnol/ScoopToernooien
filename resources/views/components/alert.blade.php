@props(['type' => 'success', 'header' => null])

@if($slot->isNotEmpty())
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 10000)"
         {{ $attributes->merge(['class' => 'fixed top-2 inset-x-0 z-50 mx-auto max-w-3xl rounded-lg p-4 mb-4 shadow-lg shadow-black/10 dark:shadow-black/30 ' . ($type === 'error' ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800')]) }}
         role="alert"
         style="left: env(safe-area-inset-left); right: env(safe-area-inset-right);">
        <button @click="show = false" type="button" class="absolute top-2 right-3 text-sm font-semibold">
            &times;
        </button>
        @if($header)
            <h3 class="text-lg font-medium mb-2">{{ $header }}</h3>
        @endif
        <div class="text-sm">
            {{ $slot }}
        </div>
        @if(isset($actions))
            <div class="mt-4">
                {{ $actions }}
            </div>
        @endif
    </div>
@endif
