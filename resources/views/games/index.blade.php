@php use Illuminate\Support\Carbon; use Illuminate\Support\Str; @endphp
<x-layouts.admin>
    <div data-admin class="space-y-6">
        @if (session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="xl" class="font-display">{{ __('Games') }}</flux:heading>
                <flux:subheading>{{ __('Create matches, record results, and resolve conflicts.') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-3">
                <flux:link :href="route('dashboard.generateSchedule')" wire:navigate>{{ __('Generate schedule') }}</flux:link>
                <flux:link :href="route('dashboard.schedule')" wire:navigate>{{ __('View schedule') }}</flux:link>
            </div>
        </div>

        @php $conflictCount = ($conflicts ?? null) ? $conflicts->count() : 0; @endphp
        @if($conflictCount > 0)
            <div id="conflicts" class="space-y-4">
                <flux:callout variant="warning" icon="exclamation-triangle">
                    <flux:callout.heading>{{ __('Attention needed') }}</flux:callout.heading>
                    <flux:callout.text>{{ $conflictCount }} {{ __('conflicting game') }} {{ Str::plural(__('result'), $conflictCount) }} {{ __('require admin action.') }}</flux:callout.text>
                </flux:callout>

                @foreach($conflicts as $cg)
                    @php
                        $t1 = optional($cg->team1()->first())->name ?? ('Team #'.$cg->team_1_id);
                        $t2 = optional($cg->team2()->first())->name ?? ('Team #'.$cg->team_2_id);
                        $seed = preg_match('/^(\d+)-(\d+)$/', $cg->team_1_submission ?? '', $m)
                            || preg_match('/^(\d+)-(\d+)$/', $cg->team_2_submission ?? '', $m)
                            ? $m : null;
                    @endphp
                    <x-ui.card class="border-amber-300 dark:border-amber-400/40">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="space-y-1">
                                <div class="font-semibold">{{ $t1 }} vs {{ $t2 }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400" id="game-submissions-{{ $cg->id }}">
                                    {{ __('Team submissions') }}:
                                    <flux:badge color="amber" size="sm">{{ $t1 }} {{ $cg->team_1_submission ?? '—' }}</flux:badge>
                                    <span class="mx-1">≠</span>
                                    <flux:badge color="amber" size="sm">{{ $t2 }} {{ $cg->team_2_submission ?? '—' }}</flux:badge>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.games.approve', $cg->id) }}"
                                  class="flex flex-col gap-2 md:flex-row md:items-end md:gap-3"
                                  data-confirm="score"
                                  data-confirm-team1="{{ $t1 }}"
                                  data-confirm-team2="{{ $t2 }}"
                                  data-confirm-current-final="—"
                                  data-confirm-team1-submission="{{ $cg->team_1_submission ?? '—' }}"
                                  data-confirm-team2-submission="{{ $cg->team_2_submission ?? '—' }}"
                                  data-confirm-status-target="status-{{ $cg->id }}">
                                @csrf
                                <x-ui.score-input :team1="$t1" :team2="$t2" size="sm"
                                                   :team1Value="$seed[1] ?? null" :team2Value="$seed[2] ?? null" />
                                <flux:button type="submit" variant="primary" size="sm">{{ __('Approve') }}</flux:button>
                            </form>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('games.store') }}" class="mb-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:select name="team_1_id" :label="__('Team 1')">
                        @foreach($teams as $team)
                            <flux:select.option value="{{ $team->id }}">{{ $team->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select name="team_2_id" :label="__('Team 2')">
                        @foreach($teams as $team)
                            <flux:select.option value="{{ $team->id }}">{{ $team->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input name="start_time" :label="__('Start Time')" required value="{{ old('start_time') }}" placeholder="13:00" />
                    <flux:input name="end_time" :label="__('End Time')" required value="{{ old('end_time') }}" placeholder="13:15" />
                </div>
                <flux:input type="number" name="field" :label="__('Field')" required min="0" value="{{ old('field') }}" class="md:w-48" />
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">{{ __('Create Game') }}</flux:button>
                </div>
            </form>

            <flux:separator class="mb-6" />

            @php
                $withOutcomes = $games->filter(function($g){ return !empty($g->accepted_outcome) || !empty($g->team_1_submission) || !empty($g->team_2_submission); });
                $withoutOutcomes = $games->filter(function($g){ return empty($g->accepted_outcome) && empty($g->team_1_submission) && empty($g->team_2_submission); });
            @endphp

            <div class="space-y-6">
                <div>
                    <flux:subheading class="mb-3">{{ __('Games with outcomes/submissions') }}</flux:subheading>
                    <div class="space-y-3">
                        @forelse($withOutcomes as $game)
                            @php
                                $t1 = optional($game->team1()->first())->name ?? ('Team #'.$game->team_1_id);
                                $t2 = optional($game->team2()->first())->name ?? ('Team #'.$game->team_2_id);
                                $status = $game->accepted_outcome ? 'final' : ($game->status === 'conflict' ? 'conflict' : 'pending');
                                $seed = preg_match('/^(\d+)-(\d+)$/', $game->accepted_outcome ?? $game->team_1_submission ?? $game->team_2_submission ?? '', $m) ? $m : null;
                            @endphp
                            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" data-game-row>
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                                    <div>{{ $t1 }} vs {{ $t2 }}</div>
                                    <x-ui.status-badge id="status-{{ $game->id }}" :status="$status">
                                        @if($game->accepted_outcome)
                                            {{ __('Final') }}: {{ $game->accepted_outcome }}
                                        @elseif($status === 'conflict')
                                            {{ __('Conflict') }}
                                        @else
                                            {{ __('Pending') }}
                                        @endif
                                    </x-ui.status-badge>
                                </div>

                                <form method="POST" action="{{ route('admin.games.approve', $game->id) }}"
                                      class="flex flex-col gap-2 md:flex-row md:items-end md:gap-3"
                                      data-confirm="score"
                                      data-confirm-team1="{{ $t1 }}"
                                      data-confirm-team2="{{ $t2 }}"
                                      data-confirm-current-final="{{ $game->accepted_outcome ?? '—' }}"
                                      data-confirm-team1-submission="{{ $game->team_1_submission ?? '—' }}"
                                      data-confirm-team2-submission="{{ $game->team_2_submission ?? '—' }}"
                                      data-confirm-status-target="status-{{ $game->id }}">
                                    @csrf
                                    <x-ui.score-input :team1="$t1" :team2="$t2" size="sm"
                                                       :team1Value="$seed[1] ?? null" :team2Value="$seed[2] ?? null" />
                                    <flux:button type="submit" variant="primary" size="sm">{{ __('Update final score') }}</flux:button>
                                </form>

                                <form id="delete-game-{{ $game->id }}" method="POST" action="{{ route('games.destroy', $game->id) }}"
                                      class="hidden" data-ajax="delete" data-remove-target="closest:[data-game-row]">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        @empty
                            <x-ui.empty-state :message="__('No games with outcomes yet.')" />
                        @endforelse
                    </div>
                </div>

                <div>
                    <flux:subheading class="mb-3">{{ __('Games without outcomes') }}</flux:subheading>
                    <div class="space-y-3">
                        @forelse($withoutOutcomes as $game)
                            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" data-game-row>
                                <div class="mb-3 text-sm">{{ optional($game->team1()->first())->name ?? ('Team #'.$game->team_1_id) }}
                                    vs {{ optional($game->team2()->first())->name ?? ('Team #'.$game->team_2_id) }}</div>
                                <form method="POST" action="{{ route('games.update', $game->id) }}"
                                      class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-5"
                                      data-ajax="save">
                                    @csrf
                                    @method('PUT')
                                    <flux:select name="team_1_id" :label="__('Team 1')">
                                        @foreach($teams as $team)
                                            <flux:select.option value="{{ $team->id }}" :selected="$team->id === $game->team_1_id">{{ $team->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:select name="team_2_id" :label="__('Team 2')">
                                        @foreach($teams as $team)
                                            <flux:select.option value="{{ $team->id }}" :selected="$team->id === $game->team_2_id">{{ $team->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:input name="start_time" :label="__('Start')"
                                                value="{{ Carbon::hasFormat($game->start_time, 'H:i') ? $game->start_time : (optional(Carbon::parse($game->start_time, null))->format('H:i') ?? (preg_match('/\\d{2}:\\d{2}/', $game->start_time, $m) ? $m[0] : $game->start_time)) }}" />
                                    <flux:input name="end_time" :label="__('End')"
                                                value="{{ Carbon::hasFormat($game->end_time, 'H:i') ? $game->end_time : (optional(Carbon::parse($game->end_time, null))->format('H:i') ?? (preg_match('/\\d{2}:\\d{2}/', $game->end_time, $m) ? $m[0] : $game->end_time)) }}" />
                                    <flux:input type="number" name="field" :label="__('Field')" value="{{ $game->field + 1 }}" />
                                    <div class="flex items-end justify-end gap-2 sm:col-span-2 md:col-span-5">
                                        <flux:button type="submit" variant="primary" size="sm">{{ __('Save') }}</flux:button>
                                        <flux:button type="submit" form="delete-game-{{ $game->id }}" variant="danger" size="sm"
                                                     onclick="return confirm('{{ __('Delete this game?') }}');">{{ __('Delete') }}</flux:button>
                                    </div>
                                </form>
                                <form id="delete-game-{{ $game->id }}" method="POST" action="{{ route('games.destroy', $game->id) }}"
                                      class="hidden" data-ajax="delete" data-remove-target="closest:[data-game-row]">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        @empty
                            <x-ui.empty-state :message="__('All games have outcomes or submissions.')" />
                        @endforelse
                    </div>
                </div>
            </div>
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

    @include('games._confirm-modal')
</x-layouts.admin>
