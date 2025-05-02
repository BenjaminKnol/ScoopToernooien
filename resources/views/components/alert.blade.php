@props(['type' => 'success', 'header' => null])

@if($slot->isNotEmpty())
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 10000)"
         {{ $attributes->merge(['class' => 'rounded-lg p-4 mb-4 relative ' . ($type === 'error' ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800')]) }}
         role="alert">
        <button @click="show = false" type="button" class="absolute top-2 right-2 text-sm font-semibold">
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
