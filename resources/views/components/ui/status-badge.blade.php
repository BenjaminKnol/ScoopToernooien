@props(['status'])

@php
    [$color, $label] = match ($status) {
        'pending' => ['blue', __('Pending')],
        'conflict' => ['amber', __('Conflict')],
        'final' => ['green', __('Final')],
        'won' => ['green', __('Won')],
        'lost' => ['red', __('Lost')],
        'draw' => ['zinc', __('Draw')],
        default => ['zinc', $status],
    };
@endphp

<flux:badge {{ $attributes }} :color="$color" size="sm">{{ $slot->isEmpty() ? $label : $slot }}</flux:badge>
