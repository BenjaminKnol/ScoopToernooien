@php
    /** @var array $defaults */
    /** @var int $numberOfFields */
    /** @var float|int $availableHours */
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
            <flux:heading size="xl" class="font-display">{{ __('Generate schedule') }}</flux:heading>
            <flux:subheading>{{ __('Set your constraints and generate a proposed schedule.') }}</flux:subheading>
        </div>

        <x-ui.card>
                <form method="POST" action="{{ route('dashboard.generateSchedule.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <flux:input type="number" min="5" max="180" name="match_length_minutes"
                                    :label="__('Match length (minutes)')"
                                    value="{{ old('match_length_minutes', $defaults['match_length_minutes']) }}" required />
                        <flux:input type="number" min="1" max="10" name="max_consecutive_matches"
                                    :label="__('Max consecutive matches (per team)')"
                                    value="{{ old('max_consecutive_matches', $defaults['max_consecutive_matches']) }}" required />
                        <flux:input type="number" min="0" max="10" name="max_idle_breaks"
                                    :label="__('Max idle breaks allowed (no game in between)')"
                                    value="{{ old('max_idle_breaks', $defaults['max_idle_breaks']) }}" required />
                        <flux:input type="number" min="1" max="20" name="number_of_fields"
                                    :label="__('Number of fields')"
                                    value="{{ old('number_of_fields', $defaults['number_of_fields'] ?? $numberOfFields) }}" />
                        <flux:input type="number" step="0.25" min="0.5" max="24" name="available_hours"
                                    :label="__('Available hours')"
                                    value="{{ old('available_hours', $defaults['available_hours'] ?? $availableHours) }}" />
                    </div>

                    <div class="pt-2">
                        <flux:button type="submit" variant="primary">{{ __('Generate') }}</flux:button>
                    </div>
                </form>

                @if($result)
                    @include('dashboard._schedule-result', ['result' => $result, 'showViolations' => true])

                    <form method="POST" action="{{ route('dashboard.generateSchedule.apply') }}" class="mt-6">
                        @csrf
                        @php
                            // Build the list of games to persist from the generated schedule
                            $gamesToApply = [];
                            foreach (($result['schedule'] ?? []) as $fieldNum => $slots) {
                                foreach ($slots as $slot) {
                                    if (!empty($slot['team_1_id']) && !empty($slot['team_2_id'])) {
                                        $gamesToApply[] = [
                                            'team_1_id' => $slot['team_1_id'],
                                            'team_2_id' => $slot['team_2_id'],
                                            'start_time' => $slot['start'],
                                            'end_time' => $slot['end'],
                                            // store zero-based field index to match dashboard display (+1 shown there)
                                            'field' => max(0, (int)$fieldNum - 1),
                                        ];
                                    }
                                }
                            }
                        @endphp
                        <input type="hidden" name="games" value='@json($gamesToApply)'>
                        <div class="flex items-center gap-3">
                            <flux:checkbox name="clear_existing" value="1" :checked="true" :label="__('Remove existing games first')" />
                            <flux:button type="submit" variant="primary" size="sm">
                                {{ __('Apply and save schedule') }}
                            </flux:button>
                        </div>
                    </form>
                @endif
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
