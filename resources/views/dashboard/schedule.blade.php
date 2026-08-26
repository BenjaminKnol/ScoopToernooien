@php
    /** @var array|null $result */
@endphp

<x-layouts.admin>
    <div class="space-y-6">
        @if (session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        <div>
            <flux:heading size="xl" class="font-display">{{ __('Schedule') }}</flux:heading>
            <flux:subheading>{{ __('The current saved schedule.') }}</flux:subheading>
        </div>

        <x-ui.card>
            @if(!$result || empty($result['schedule']))
                <x-ui.empty-state :message="__('No scheduled games found. Use the generator to create a schedule or add games manually.')" />
            @else
                @include('dashboard._schedule-result', ['result' => $result, 'showViolations' => false])
            @endif
        </x-ui.card>
    </div>
</x-layouts.admin>
