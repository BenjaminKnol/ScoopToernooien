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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('dashboard.generateSchedule.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700">{{ __('Match length (minutes)') }}</label>
                            <input type="number" min="5" max="180" name="match_length_minutes"
                                   value="{{ old('match_length_minutes', $defaults['match_length_minutes']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('match_length_minutes')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700">{{ __('Max consecutive matches (per team)') }}</label>
                            <input type="number" min="1" max="10" name="max_consecutive_matches"
                                   value="{{ old('max_consecutive_matches', $defaults['max_consecutive_matches']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('max_consecutive_matches')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700">{{ __('Max idle breaks allowed (no game in between)') }}</label>
                            <input type="number" min="0" max="10" name="max_idle_breaks"
                                   value="{{ old('max_idle_breaks', $defaults['max_idle_breaks']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('max_idle_breaks')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700">{{ __('Number of fields (from .env)') }}</label>
                            <input type="number" value="{{ $numberOfFields }}"
                                   class="mt-1 block w-full border rounded p-2 bg-gray-100" readonly/>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700">{{ __('Available hours (from .env)') }}</label>
                            <input type="text" value="{{ $availableHours }}"
                                   class="mt-1 block w-full border rounded p-2 bg-gray-100" readonly/>
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
                        <h3 class="text-lg font-semibold mb-2">{{ __('Schedule summary') }}</h3>
                        <ul class="list-disc ml-6 text-sm text-gray-700">
                            <li>{{ __('Fields') }}: {{ $result['summary']['number_of_fields'] }}</li>
                            <li>{{ __('Available hours') }}: {{ $result['summary']['available_hours'] }}</li>
                            <li>{{ __('Match length (minutes)') }}
                                : {{ $result['summary']['match_length_minutes'] }}</li>
                            <li>{{ __('Slots per field') }}: {{ $result['summary']['slots_per_field'] }}</li>
                            <li>{{ __('Total slots (all fields)') }}: {{ $result['summary']['total_slots'] }}</li>
                            <li>{{ __('Max consecutive matches') }}
                                : {{ $result['summary']['constraints']['max_consecutive_matches'] }}</li>
                            <li>{{ __('Max idle breaks') }}
                                : {{ $result['summary']['constraints']['max_idle_breaks'] }}</li>
                        </ul>

                        @if(empty($result['schedule']))
                            <p class="mt-4 text-gray-600 text-sm">{{ __('A detailed scheduling algorithm can be added here. For now, the capacity and constraints are summarized above.') }}</p>
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
