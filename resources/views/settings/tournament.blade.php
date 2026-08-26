@php
    /** @var \App\Models\TournamentSetting $settings */
@endphp
<x-layouts.admin>
    <div class="space-y-6">
        @if (session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        <div>
            <flux:heading size="xl" class="font-display">{{ __('Tournament settings') }}</flux:heading>
            <flux:subheading>{{ __('Core details used when generating the schedule.') }}</flux:subheading>
        </div>

        <x-ui.card>
            <form method="POST" action="{{ route('tournament-settings.update') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input type="number" min="1" max="50" name="number_of_fields"
                                :label="__('Number of fields')"
                                value="{{ old('number_of_fields', $settings->number_of_fields) }}" required />
                    <flux:input type="number" step="0.25" min="0.5" max="48" name="available_hours"
                                :label="__('Available hours')"
                                value="{{ old('available_hours', $settings->available_hours) }}" required />
                    <flux:input type="date" name="tournament_day"
                                :label="__('Tournament day')"
                                value="{{ old('tournament_day', optional($settings->tournament_day)->format('Y-m-d')) }}" required />
                    <flux:input type="text" name="start_time"
                                :label="__('Start time')"
                                value="{{ old('start_time', $settings->start_time) }}"
                                placeholder="13:00" required />
                </div>
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">{{ __('Save settings') }}</flux:button>
                </div>
            </form>
        </x-ui.card>

        @if ($errors->any())
            <x-alert type="error">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
    </div>
</x-layouts.admin>
