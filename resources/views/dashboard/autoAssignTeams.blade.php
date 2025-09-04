@php
    /** @var array $defaults */
    /** @var array|null $result */
@endphp

<x-layouts.app.header>
    @if (session('success'))
        <x-alert type="success">
            {{ session('success') }}
        </x-alert>
    @endif

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('dashboard.autoAssignTeams.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">{{ __('Target players per team') }}</label>
                            <input type="number" min="1" name="target_team_size"
                                   value="{{ old('target_team_size', $defaults['target_team_size']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('target_team_size')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">{{ __('Max team size variance') }}</label>
                            <input type="number" min="0" name="max_team_size_variance"
                                   value="{{ old('max_team_size_variance', $defaults['max_team_size_variance']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('max_team_size_variance')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">{{ __('Gender weight (H bonus)') }}</label>
                            <input type="number" step="0.1" min="0" max="10" name="gender_weight"
                                   value="{{ old('gender_weight', $defaults['gender_weight']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('gender_weight')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-200">{{ __('Team code weight') }}</label>
                            <input type="number" step="0.1" min="0" max="10" name="code_weight"
                                   value="{{ old('code_weight', $defaults['code_weight']) }}"
                                   class="mt-1 block w-full border rounded p-2" required/>
                            @error('code_weight')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2 flex items-center gap-2 mt-2">
                            <input type="checkbox" name="reassign_existing" id="reassign_existing" value="1" {{ old('reassign_existing', $defaults['reassign_existing']) ? 'checked' : '' }}>
                            <label for="reassign_existing" class="text-sm text-gray-700 dark:text-zinc-200">{{ __('Reassign already assigned players (ignore current teams)') }}</label>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">{{ __('Auto-assign') }}</button>
                        <a href="{{ route('dashboard') }}"
                           class="ml-2 text-gray-600 hover:text-gray-800">{{ __('Back to dashboard') }}</a>
                    </div>
                </form>

                <div class="mt-6 text-sm text-zinc-700 dark:text-zinc-300">
                    <p class="mb-2">{{ __('How it works:') }}</p>
                    <ul class="list-disc ml-6 space-y-1">
                        <li>{{ __('We compute a simple score per player based on their team code (e.g., H1, D3) and a small gender bonus for H (men), both configurable above.') }}</li>
                        <li>{{ __('Players are sorted by score and greedily assigned to the lowest-total-score team, keeping team sizes within the target ± variance where possible.') }}</li>
                        <li>{{ __('This keeps teams balanced in both size and overall strength, with diminishing differences between codes (e.g., H1 vs H6).') }}</li>
                    </ul>
                </div>

                @if($result)
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-2 text-zinc-900 dark:text-zinc-100">{{ __('Assignment summary') }}</h3>
                        <ul class="list-disc ml-6 text-sm text-zinc-800 dark:text-zinc-200">
                            <li>{{ __('Target size') }}: {{ $result['target'] }} ± {{ $result['variance'] }}</li>
                            <li>{{ __('Gender weight') }}: {{ $result['gender_weight'] }}</li>
                            <li>{{ __('Team code weight') }}: {{ $result['code_weight'] }}</li>
                            <li>{{ __('Reassigned all') }}: {{ $result['reassigned'] ? 'yes' : 'no' }}</li>
                        </ul>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full border border-zinc-200 dark:border-zinc-700 text-xs">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="border-b border-zinc-200 dark:border-zinc-700 px-2 py-1 text-left text-zinc-700 dark:text-zinc-200">{{ __('Team') }}</th>
                                    <th class="border-b border-l border-zinc-200 dark:border-zinc-700 px-2 py-1 text-zinc-700 dark:text-zinc-200">{{ __('Players') }}</th>
                                    <th class="border-b border-l border-zinc-200 dark:border-zinc-700 px-2 py-1 text-zinc-700 dark:text-zinc-200">{{ __('Total score') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($result['teams'] as $t)
                                    <tr>
                                        <td class="border-t border-zinc-200 dark:border-zinc-700 px-2 py-1 text-zinc-800 dark:text-zinc-200">{{ $t['name'] }}</td>
                                        <td class="border-t border-l border-zinc-200 dark:border-zinc-700 px-2 py-1 text-right text-zinc-700 dark:text-zinc-300">{{ $t['count'] }}</td>
                                        <td class="border-t border-l border-zinc-200 dark:border-zinc-700 px-2 py-1 text-right text-zinc-700 dark:text-zinc-300">{{ $t['score'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            <h4 class="text-md font-semibold mb-2 text-zinc-900 dark:text-zinc-100">{{ __('Proposed team members') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($result['teams_detailed'] as $tid => $agg)
                                    <div class="border rounded p-3 dark:border-zinc-700">
                                        <div class="font-medium mb-1">{{ $agg['name'] }}</div>
                                        <div class="text-xs text-zinc-500 mb-2">{{ __('Players') }}: {{ $agg['count'] }} · {{ __('Score') }}: {{ number_format($agg['score'],2) }}</div>
                                        @if(empty($agg['members']))
                                            <div class="text-xs text-zinc-500">{{ __('No players') }}</div>
                                        @else
                                            <ul class="text-sm space-y-1">
                                                @foreach($agg['members'] as $m)
                                                    <li>
                                                        <span>{{ $m['name'] }}</span>
                                                        <span class="text-xs text-zinc-500">(score {{ number_format($m['score'],2) }})</span>
                                                        <span class="ml-1 text-xs text-zinc-500">Scoop team: {{ $m['team_code'] ?? '—' }}</span>
                                                        <span class="ml-1 text-xs text-zinc-500">{{ __('current:') }} {{ $m['current_team_name'] ?? '—' }}</span>
                                                        @if(!empty($m['locked']))
                                                            <span class="ml-1 inline-block text-[10px] px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ __('existing') }}</span>
                                                        @else
                                                            <span class="ml-1 inline-block text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ __('new') }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <form method="POST" action="{{ route('dashboard.autoAssignTeams.apply') }}" class="mt-6">
                            @csrf
                            <input type="hidden" name="proposal" value='@json($result['proposal'])'>
                            <input type="hidden" name="reassign_existing" value="{{ $defaults['reassign_existing'] ? 1 : 0 }}">
                            <div class="flex items-center gap-2">
                                <button type="submit" class="inline-flex justify-center rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">{{ __('Accept and apply changes') }}</button>
                                <a href="{{ route('dashboard.autoAssignTeams') }}" class="text-xs text-gray-600 hover:text-gray-800">{{ __('Change parameters') }}</a>
                            </div>
                        </form>
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
