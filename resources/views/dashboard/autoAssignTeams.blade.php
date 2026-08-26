@php
    /** @var array $defaults */
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
            <flux:heading size="xl" class="font-display">{{ __('Auto-assign teams') }}</flux:heading>
            <flux:subheading>{{ __('Preview a balanced team assignment before applying it.') }}</flux:subheading>
        </div>

        <x-ui.card>
                <form method="POST" action="{{ route('dashboard.autoAssignTeams.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <flux:input type="number" min="1" name="target_team_size"
                                    :label="__('Target players per team')"
                                    value="{{ old('target_team_size', $defaults['target_team_size']) }}" required />
                        <flux:input type="number" min="0" name="max_team_size_variance"
                                    :label="__('Max team size variance')"
                                    value="{{ old('max_team_size_variance', $defaults['max_team_size_variance']) }}" required />
                        <flux:input type="number" step="0.1" min="0" max="10" name="gender_weight"
                                    :label="__('Gender weight (H bonus)')"
                                    value="{{ old('gender_weight', $defaults['gender_weight']) }}" required />
                        <flux:input type="number" step="0.1" min="0" max="10" name="code_weight"
                                    :label="__('Team code weight')"
                                    value="{{ old('code_weight', $defaults['code_weight']) }}" required />
                        <flux:checkbox class="md:col-span-2 mt-2" name="reassign_existing" value="1"
                                       :checked="old('reassign_existing', $defaults['reassign_existing'])"
                                       :label="__('Reassign already assigned players (ignore current teams)')" />
                    </div>

                    <div class="pt-2">
                        <flux:button type="submit" variant="primary">{{ __('Auto-assign') }}</flux:button>
                    </div>
                </form>

                <div class="mt-6 text-sm text-zinc-700 dark:text-zinc-300">
                    <p class="mb-2">{{ __('How it works:') }}</p>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>{{ __('We compute a simple score per player based on their team code (e.g., H1, D3) and a small gender bonus for H (men), both configurable above.') }}</li>
                        <li>{{ __('Players are sorted by score and greedily assigned to the lowest-total-score team, keeping team sizes within the target ± variance where possible.') }}</li>
                        <li>{{ __('This keeps teams balanced in both size and overall strength, with diminishing differences between codes (e.g., H1 vs H6).') }}</li>
                    </ul>
                </div>

                @if($result)
                    <div class="mt-8 space-y-6">
                        <div>
                            <flux:heading size="lg" class="mb-2">{{ __('Assignment summary') }}</flux:heading>
                            <ul class="list-disc space-y-1 pl-5 text-sm text-zinc-800 dark:text-zinc-200">
                                <li>{{ __('Target size') }}: {{ $result['target'] }} ± {{ $result['variance'] }}</li>
                                <li>{{ __('Gender weight') }}: {{ $result['gender_weight'] }}</li>
                                <li>{{ __('Team code weight') }}: {{ $result['code_weight'] }}</li>
                                <li>{{ __('Reassigned all') }}: {{ $result['reassigned'] ? __('yes') : __('no') }}</li>
                            </ul>
                        </div>

                        <x-ui.data-table>
                            <x-slot:head>
                                <th class="px-2 py-1">{{ __('Team') }}</th>
                                <th class="px-2 py-1 text-right">{{ __('Players') }}</th>
                                <th class="px-2 py-1 text-right">{{ __('Total score') }}</th>
                            </x-slot:head>
                            @foreach($result['teams'] as $t)
                                <tr>
                                    <td class="px-2 py-1">{{ $t['name'] }}</td>
                                    <td class="px-2 py-1 text-right">{{ $t['count'] }}</td>
                                    <td class="px-2 py-1 text-right">{{ $t['score'] }}</td>
                                </tr>
                            @endforeach
                        </x-ui.data-table>

                        <div>
                            <flux:heading size="lg" class="mb-2">{{ __('Proposed team members') }}</flux:heading>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach($result['teams_detailed'] as $tid => $agg)
                                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                        <div class="mb-1 font-medium">{{ $agg['name'] }}</div>
                                        <div class="mb-2 text-xs text-zinc-500">{{ __('Players') }}: {{ $agg['count'] }} · {{ __('Score') }}: {{ number_format($agg['score'],2) }}</div>
                                        @if(empty($agg['members']))
                                            <x-ui.empty-state class="text-xs" :message="__('No players')" />
                                        @else
                                            <ul class="space-y-1 text-sm">
                                                @foreach($agg['members'] as $m)
                                                    <li class="flex flex-wrap items-center gap-1">
                                                        <span>{{ $m['name'] }}</span>
                                                        <span class="text-xs text-zinc-500">({{ __('score') }} {{ number_format($m['score'],2) }})</span>
                                                        <span class="text-xs text-zinc-500">{{ __('Scoop team') }}: {{ $m['team_code'] ?? '—' }}</span>
                                                        <span class="text-xs text-zinc-500">{{ __('current:') }} {{ $m['current_team_name'] ?? '—' }}</span>
                                                        @if(!empty($m['locked']))
                                                            <flux:badge color="zinc" size="sm">{{ __('existing') }}</flux:badge>
                                                        @else
                                                            <flux:badge color="indigo" size="sm">{{ __('new') }}</flux:badge>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <form method="POST" action="{{ route('dashboard.autoAssignTeams.apply') }}">
                            @csrf
                            <input type="hidden" name="proposal" value='@json($result['proposal'])'>
                            <input type="hidden" name="reassign_existing" value="{{ $defaults['reassign_existing'] ? 1 : 0 }}">
                            <div class="flex items-center gap-3">
                                <flux:button type="submit" variant="primary" size="sm">{{ __('Accept and apply changes') }}</flux:button>
                                <flux:link :href="route('dashboard.autoAssignTeams')" wire:navigate>{{ __('Change parameters') }}</flux:link>
                            </div>
                        </form>
                    </div>
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
