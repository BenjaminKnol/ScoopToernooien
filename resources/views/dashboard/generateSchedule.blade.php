@php
    /** @var array $defaults */
    /** @var int $numberOfFields */
    /** @var float|int $availableHours */
    /** @var array|null $result */
@endphp

<x-layouts.app.header>
    @if (session('success'))
        <x-alert type="success">
            {{ session('success') }}
        </x-alert>
    @endif

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('dashboard.generateSchedule.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-zinc-200">{{ __('Match length (minutes)') }}</label>
                            <input type="number" min="5" max="180" name="match_length_minutes"
                                   value="{{ old('match_length_minutes', $defaults['match_length_minutes']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('match_length_minutes')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-zinc-200">{{ __('Max consecutive matches (per team)') }}</label>
                            <input type="number" min="1" max="10" name="max_consecutive_matches"
                                   value="{{ old('max_consecutive_matches', $defaults['max_consecutive_matches']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('max_consecutive_matches')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-zinc-200">{{ __('Max idle breaks allowed (no game in between)') }}</label>
                            <input type="number" min="0" max="10" name="max_idle_breaks"
                                   value="{{ old('max_idle_breaks', $defaults['max_idle_breaks']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('max_idle_breaks')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-zinc-200">{{ __('Number of fields') }}</label>
                            <input type="number" min="1" max="20" name="number_of_fields"
                                   value="{{ old('number_of_fields', $defaults['number_of_fields'] ?? $numberOfFields) }}"
                                   class="mt-1 block w-full border rounded p-2"/>
                            @error('number_of_fields')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-zinc-200">{{ __('Available hours') }}</label>
                            <input type="number" step="0.25" min="0.5" max="24" name="available_hours"
                                   value="{{ old('available_hours', $defaults['available_hours'] ?? $availableHours) }}"
                                   class="mt-1 block w-full border rounded p-2"/>
                            @error('available_hours')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">{{ __('Generate') }}</button>
                        <a href="{{ route('dashboard') }}"
                           class="ml-2 text-gray-600 hover:text-gray-800">{{ __('Back to dashboard') }}</a>
                    </div>
                </form>

                @if($result)
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-2 text-zinc-900 dark:text-zinc-100">{{ __('Schedule summary') }}</h3>
                        <ul class="list-disc ml-6 text-sm text-zinc-800 dark:text-zinc-200">
                            <li>{{ __('Fields') }}: {{ $result['summary']['number_of_fields'] }}</li>
                            <li>{{ __('Available hours') }}: {{ $result['summary']['available_hours'] }}</li>
                            <li>{{ __('Match length (minutes)') }}
                                : {{ $result['summary']['match_length_minutes'] }}</li>
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
                            <li>{{ __('Max consecutive matches') }}
                                : {{ $result['summary']['constraints']['max_consecutive_matches'] }}</li>
                            <li>{{ __('Max idle breaks') }}
                                : {{ $result['summary']['constraints']['max_idle_breaks'] }}</li>
                            <li>{{ __('Regeneration attempts used') }}: {{ $result['summary']['regeneration']['attempts_used'] ?? 1 }}</li>
                            <li>{{ __('Max teams idle in any slot') }}: {{ $result['summary']['regeneration']['max_idle_in_any_slot'] ?? '-' }}</li>
                            @if(!empty($result['summary']['violations']) && ($result['summary']['violations']['unplaced_pairings'] ?? 0) > 0)
                                <li class="text-red-600 dark:text-red-400">{{ __('Unplaced pairings due to constraints') }}: {{ $result['summary']['violations']['unplaced_pairings'] }}</li>
                            @endif
                            @php $suggestions = $result['summary']['suggestions'] ?? []; @endphp
                            @if(!empty($suggestions))
                                <li class="mt-2">
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Suggestions') }}</div>
                                    <ul class="list-disc ml-5 space-y-0.5">
                                        @foreach($suggestions as $s)
                                            <li class="text-amber-700 dark:text-amber-400">{{ $s }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        </ul>

                        @if(empty($result['schedule']))
                            <p class="mt-4 text-gray-600 text-sm">{{ __('A detailed scheduling algorithm can be added here. For now, the capacity and constraints are summarized above.') }}</p>
                        @else
                            <div class="mt-4">
                                <div class="text-sm text-zinc-700 dark:text-zinc-300 mb-2 font-medium">{{ __('Scheduling window') }}: {{ $result['summary']['window']['start'] }} → {{ $result['summary']['window']['end'] }}</div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($result['schedule'] as $field => $slots)
                                        <div class="border rounded p-3 dark:border-zinc-700">
                                            <div class="font-semibold mb-2 text-zinc-900 dark:text-zinc-100">{{ __('Field') }} {{ $field }}</div>
                                            @if(count($slots) === 0)
                                                <div class="text-xs text-gray-500">{{ __('No slots in window') }}</div>
                                            @else
                                                <ul class="text-xs space-y-1 text-zinc-800 dark:text-zinc-200">
                                                    @foreach($slots as $slot)
                                                        <li>
                                                            <span class="font-medium">{{ $slot['start_hm'] }} - {{ $slot['end_hm'] }}</span>
                                                            @if($slot['team_1_name'] && $slot['team_2_name'])
                                                                · <span>{{ $slot['team_1_name'] }}</span>
                                                                <span class="text-zinc-400">vs</span>
                                                                <span>{{ $slot['team_2_name'] }}</span>
                                                                @if(!empty($slot['violations']['repeat_opponent']))
                                                                    <span class="ml-2 inline-block text-[10px] px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">{{ __('repeat') }}</span>
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

                            <div class="mt-8">
                                <h4 class="text-md font-semibold mb-2 text-zinc-900 dark:text-zinc-100">{{ __('Team distribution (matrix)') }}</h4>
                                <div class="mb-2 text-xs text-zinc-700 dark:text-zinc-300 space-x-3">
                                    <span class="inline-flex items-center"><span class="inline-block w-3 h-3 bg-red-500 dark:bg-red-600 mr-1"></span>{{ __('Game') }}</span>
                                    <span class="inline-flex items-center"><span class="inline-block w-3 h-3 bg-green-500/40 dark:bg-green-600/40 mr-1"></span>{{ __('Break') }}</span>
                                    <span class="inline-flex items-center"><span class="inline-block w-3 h-3 bg-red-500 dark:bg-red-600 ring-2 ring-orange-500 mr-1"></span>{{ __('Repeat opponent early') }}</span>
                                    <span class="inline-flex items-center"><span class="inline-block w-3 h-3 bg-green-500/40 dark:bg-green-600/40 ring-2 ring-amber-500 mr-1"></span>{{ __('Over max breaks') }}</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border border-zinc-200 dark:border-zinc-700 text-xs">
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
                                                        $class = $plays ? 'bg-red-500 dark:bg-red-600' : 'bg-green-500/40 dark:bg-green-600/40';
                                                        if (!empty($flags['repeat_opponent'])) { $class .= ' ring-2 ring-orange-500'; }
                                                        if (!empty($flags['over_breaks'])) { $class .= ' ring-2 ring-amber-500'; }
                                                    @endphp
                                                    <td class="border-t border-l border-zinc-200 dark:border-zinc-700">
                                                        <div class="h-4 w-full {{ $class }}"></div>
                                                    </td>
                                                @endforeach
                                                <td class="border-t border-l border-zinc-200 dark:border-zinc-700 text-right px-2 py-1 text-zinc-700 dark:text-zinc-300">{{ $team['count'] }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8">
                                <h4 class="text-md font-semibold mb-2 text-zinc-900 dark:text-zinc-100">{{ __('Field utilization (matrix)') }}</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border border-zinc-200 dark:border-zinc-700 text-xs">
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
                                                        <div class="h-4 w-full {{ $used ? 'bg-red-500 dark:bg-red-600' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
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
                @endif
            </div>
        </div>
    </div>
    @if ($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif
</x-layouts.app.header>
