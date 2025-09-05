@php
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
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Current schedule overview') }}</h2>
                    <div class="space-x-3">
                        <a href="{{ route('dashboard.generateSchedule') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Generate schedule') }}</a>
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800">{{ __('Back to dashboard') }}</a>
                    </div>
                </div>

                @if(!$result || empty($result['schedule']))
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ __('No scheduled games found. Use the generator to create a schedule or add games manually.') }}</p>
                @else
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2 text-zinc-900 dark:text-zinc-100">{{ __('Schedule summary') }}</h3>
                        <ul class="list-disc ml-6 text-sm text-zinc-800 dark:text-zinc-200">
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
                        </ul>
                        <div class="text-sm text-zinc-700 dark:text-zinc-300 mt-2 font-medium">{{ __('Scheduling window') }}: {{ $result['summary']['window']['start'] }} → {{ $result['summary']['window']['end'] }}</div>

                        <div class="mt-4">
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
                                                @php $plays = $result['team_matrix'][$teamId][$start] ?? false; @endphp
                                                <td class="border-t border-l border-zinc-200 dark:border-zinc-700">
                                                    <div class="h-4 w-full {{ $plays ? 'bg-red-500 dark:bg-red-600' : 'bg-green-500/40 dark:bg-green-600/40' }}"></div>
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
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app.header>
