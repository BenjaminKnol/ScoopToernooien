{{-- Expects: $result (array), $showViolations (bool, default false) --}}
@php $showViolations ??= false; @endphp

<div class="mt-6 space-y-8">
    <div>
        <flux:heading size="lg" class="mb-2">{{ __('Schedule summary') }}</flux:heading>
        <ul class="list-disc space-y-1 pl-5 text-sm text-zinc-700 dark:text-zinc-300">
            <li>{{ __('Fields') }}: {{ $result['summary']['number_of_fields'] }}</li>
            <li>{{ __('Available hours') }}: {{ $result['summary']['available_hours'] }}</li>
            <li>{{ __('Match length (minutes)') }}: {{ $result['summary']['match_length_minutes'] }}</li>
            <li>{{ __('Buffer between matches (minutes)') }}: {{ $result['summary']['buffer_minutes'] }}</li>
            <li>{{ __('Slots per field') }}:
                @if(is_array($result['summary']['slots_per_field']))
                    @foreach($result['summary']['slots_per_field'] as $field => $count)
                        <span class="inline-block mr-2">F{{ $field }}: {{ $count }}</span>
                    @endforeach
                @else
                    {{ $result['summary']['slots_per_field'] }}
                @endif
            </li>
            <li>{{ __('Total slots (all fields)') }}: {{ $result['summary']['total_slots'] }}</li>
            @if($showViolations)
                <li>{{ __('Max consecutive matches') }}: {{ $result['summary']['constraints']['max_consecutive_matches'] }}</li>
                <li>{{ __('Max idle breaks') }}: {{ $result['summary']['constraints']['max_idle_breaks'] }}</li>
                <li>{{ __('Regeneration attempts used') }}: {{ $result['summary']['regeneration']['attempts_used'] ?? 1 }}</li>
                <li>{{ __('Max teams idle in any slot') }}: {{ $result['summary']['regeneration']['max_idle_in_any_slot'] ?? '-' }}</li>
                @if(!empty($result['summary']['violations']) && ($result['summary']['violations']['unplaced_pairings'] ?? 0) > 0)
                    <li class="text-red-600 dark:text-red-400">{{ __('Unplaced pairings due to constraints') }}: {{ $result['summary']['violations']['unplaced_pairings'] }}</li>
                @endif
                @php $suggestions = $result['summary']['suggestions'] ?? []; @endphp
                @if(!empty($suggestions))
                    <li class="mt-2">
                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Suggestions') }}</div>
                        <ul class="list-disc space-y-0.5 pl-5">
                            @foreach($suggestions as $s)
                                <li class="text-amber-700 dark:text-amber-400">{{ $s }}</li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endif
        </ul>
    </div>

    @if(empty($result['schedule']))
        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('A detailed scheduling algorithm can be added here. For now, the capacity and constraints are summarized above.') }}</p>
    @else
        <div>
            <div class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Scheduling window') }}: {{ $result['summary']['window']['start'] }} → {{ $result['summary']['window']['end'] }}</div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @foreach($result['schedule'] as $field => $slots)
                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="mb-2 font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Field') }} {{ $field }}</div>
                        @if(count($slots) === 0)
                            <x-ui.empty-state class="text-xs" :message="__('No slots in window')" />
                        @else
                            <ul class="space-y-1 text-xs text-zinc-800 dark:text-zinc-200">
                                @foreach($slots as $slot)
                                    <li>
                                        <span class="font-medium">{{ $slot['start_hm'] }} - {{ $slot['end_hm'] }}</span>
                                        @if($slot['team_1_name'] && $slot['team_2_name'])
                                            · <span>{{ $slot['team_1_name'] }}</span>
                                            <span class="text-zinc-400">vs</span>
                                            <span>{{ $slot['team_2_name'] }}</span>
                                            @if($showViolations && !empty($slot['violations']['repeat_opponent']))
                                                <flux:badge color="orange" size="sm">{{ __('repeat') }}</flux:badge>
                                            @endif
                                        @else
                                            <span class="text-zinc-500">{{ __('(empty slot)') }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <flux:heading size="lg" class="mb-2">{{ __('Team distribution (matrix)') }}</flux:heading>
            <div class="mb-2 flex flex-wrap items-center gap-3 text-xs text-zinc-700 dark:text-zinc-300">
                <span class="inline-flex items-center"><span class="schedule-cell-busy mr-1 inline-block h-3 w-3"></span>{{ __('Game') }}</span>
                <span class="inline-flex items-center"><span class="schedule-cell-free mr-1 inline-block h-3 w-3"></span>{{ __('Break') }}</span>
                @if($showViolations)
                    <span class="inline-flex items-center"><span class="schedule-cell-busy schedule-cell-alert mr-1 inline-block h-3 w-3"></span>{{ __('Repeat opponent early') }}</span>
                    <span class="inline-flex items-center"><span class="schedule-cell-free schedule-cell-warn mr-1 inline-block h-3 w-3"></span>{{ __('Over max breaks') }}</span>
                @endif
            </div>
            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-xs">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="sticky left-0 z-10 bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 px-2 py-1 text-left text-zinc-700 dark:text-zinc-200">{{ __('Team') }}</th>
                        @foreach($result['slot_starts'] as $start => $hm)
                            <th class="border-b border-l border-zinc-200 dark:border-zinc-700 px-2 py-1 text-zinc-700 dark:text-zinc-200 whitespace-nowrap">{{ $hm }}</th>
                        @endforeach
                        <th class="border-b border-l border-zinc-200 dark:border-zinc-700 px-2 py-1 text-zinc-700 dark:text-zinc-200 whitespace-nowrap">{{ __('Games') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($result['teams'] as $teamId => $team)
                        <tr>
                            <td class="sticky left-0 z-10 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 px-2 py-1 text-zinc-800 dark:text-zinc-200 whitespace-nowrap">{{ $team['name'] }}</td>
                            @foreach($result['slot_starts'] as $start => $hm)
                                @php
                                    $plays = $result['team_matrix'][$teamId][$start] ?? false;
                                    $flags = $result['team_cell_flags'][$teamId][$start] ?? [];
                                    $class = $plays ? 'schedule-cell-busy' : 'schedule-cell-free';
                                    if ($showViolations && !empty($flags['repeat_opponent'])) { $class .= ' schedule-cell-alert'; }
                                    if ($showViolations && !empty($flags['over_breaks'])) { $class .= ' schedule-cell-warn'; }
                                @endphp
                                <td class="border-t border-l border-zinc-200 dark:border-zinc-700">
                                    <div class="{{ $class }}"></div>
                                </td>
                            @endforeach
                            <td class="border-t border-l border-zinc-200 dark:border-zinc-700 text-right px-2 py-1 text-zinc-700 dark:text-zinc-300">{{ $team['count'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <flux:heading size="lg" class="mb-2">{{ __('Field utilization (matrix)') }}</flux:heading>
            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-xs">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="sticky left-0 z-10 bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 px-2 py-1 text-left text-zinc-700 dark:text-zinc-200">{{ __('Field') }}</th>
                        @foreach($result['slot_starts'] as $start => $hm)
                            <th class="border-b border-l border-zinc-200 dark:border-zinc-700 px-2 py-1 text-zinc-700 dark:text-zinc-200 whitespace-nowrap">{{ $hm }}</th>
                        @endforeach
                        <th class="border-b border-l border-zinc-200 dark:border-zinc-700 px-2 py-1 text-zinc-700 dark:text-zinc-200 whitespace-nowrap">{{ __('Used') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach(($result['field_matrix'] ?? []) as $field => $starts)
                        <tr>
                            <td class="sticky left-0 z-10 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 px-2 py-1 text-zinc-800 dark:text-zinc-200 whitespace-nowrap">F{{ $field }}</td>
                            @foreach($result['slot_starts'] as $start => $hm)
                                @php $used = $starts[$start] ?? false; @endphp
                                <td class="border-t border-l border-zinc-200 dark:border-zinc-700">
                                    <div class="{{ $used ? 'schedule-cell-busy' : 'h-4 w-full rounded-sm bg-zinc-200 dark:bg-zinc-700' }}"></div>
                                </td>
                            @endforeach
                            <td class="border-t border-l border-zinc-200 dark:border-zinc-700 text-right px-2 py-1 text-zinc-700 dark:text-zinc-300">{{ $result['field_usage_count'][$field] ?? 0 }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
