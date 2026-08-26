@php
    use App\Models\Game;
    use Illuminate\Support\Carbon;
@endphp
<x-layouts.app.header>
    <div class="flex w-full flex-col gap-6 p-4" @if($player->team_id ?? false) style="--team-color: {{ $team->color_hex ?? 'var(--color-brand-green)' }}" @endif>
        @guest()
            <div class="grid auto-rows-min gap-4 grid-cols-1">
                <div>
                    <flux:heading size="xl" class="font-display">{{ __('My Team') }}</flux:heading>
                    <flux:subheading>{{ __('This page is only accessible when you are logged in.') }}</flux:subheading>
                </div>
            </div>
        @endguest

        @if(!$player->team_id)
            <x-ui.card>
                <p class="mb-2">{{ __("We haven't linked your account to a team yet.") }}</p>
                <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('Ask an administrator to link your account to your team, or provide guidance to implement automatic linking from sign-ups.') }}</p>
            </x-ui.card>
        @else
            <div class="flex items-center gap-3 overflow-hidden rounded-xl border-t-4 bg-white p-4 shadow-sm dark:bg-zinc-900"
                 style="border-top-color: var(--team-color);">
                <span class="h-8 w-8 shrink-0 rounded-full border border-zinc-300 dark:border-zinc-600"
                      style="background-color: var(--team-color);"></span>
                <div>
                    <flux:heading size="xl" class="font-display leading-tight">{{ $team->name }}</flux:heading>
                    @if(!empty($team->color_name))
                        <flux:subheading>{{ __('Kleur') }}: {{ $team->color_name }}</flux:subheading>
                    @endif
                </div>
            </div>

            {{--            Upcoming games--}}
            <x-ui.card :title="__('Upcoming games')">
                @php $games = $team->upcomingGames(); @endphp
                @if($games->count() > 0)
                    <ul class="space-y-1.5">
                        @foreach($games as $game)
                            <li class="flex items-center justify-between">
                                <span class="truncate">{{ Carbon::hasFormat($game->start_time, 'H:i') ? $game->start_time : (optional(Carbon::parse($game->start_time, null))->format('H:i') ?? (preg_match('/\\d{2}:\\d{2}/', $game->start_time, $m) ? $m[0] : $game->start_time)) }} · {{ $game->opponent($team->id)->name }}</span>
                                <span
                                    class="text-zinc-500 dark:text-zinc-400">{{ __('Field :n', ['n' => $game->field + 1]) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state :message="__('No upcoming games')" />
                @endif
            </x-ui.card>

            {{-- Report past game result --}}
            <x-ui.card :title="__('Report result')">
                @php
                    $pastGames = Game::where(function($q) use($team){ $q->where('team_1_id',$team->id)->orWhere('team_2_id',$team->id); })
                        ->where('start_time','<=', now())
                        ->orderByDesc('start_time')
                        ->take(10)
                        ->get();
                @endphp
                @if($pastGames->isEmpty())
                    <x-ui.empty-state :message="__('No games to report yet.')" />
                @else
                    <ul class="space-y-3">
                        @foreach($pastGames as $g)
                            <li class="rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="text-sm">
                                        <span class="font-medium">
                                            {{ Carbon::hasFormat($g->start_time, 'H:i') ? $g->start_time : (optional(Carbon::parse($g->start_time, null))->format('H:i') ?? (preg_match('/\\d{2}:\\d{2}/', $g->start_time, $m) ? $m[0] : $g->start_time)) }}
                                        </span>
                                        · {{ $g->opponent($team->id)->name }}
                                        <span class="text-zinc-500"><br>({{ __('Field :n', ['n' => $g->field + 1]) }})</span>
                                    </div>
                                    @if($g->accepted_outcome)
                                        @php
                                            $outcomes = [$t1Outcome, $t2Outcome] = explode('-', $g->accepted_outcome);
                                            $winner = max($outcomes);
                                            if($t2Outcome == $t1Outcome) {
                                                $status = 'draw';
                                            } elseif ($winner == $t1Outcome && $g->team_1_id == $team->id || $winner == $t2Outcome && $g->team_2_id == $team->id) {
                                                $status = 'won';
                                            } else {
                                                $status = 'lost';
                                            }
                                        @endphp
                                        <x-ui.status-badge :status="$status">{{ __('Result') }}: {{ $g->accepted_outcome }}</x-ui.status-badge>
                                    @else
                                        <form method="POST" action="{{ route('team.games.report', $g) }}"
                                              class="flex flex-col gap-2 sm:flex-row sm:items-end">
                                            @csrf
                                            @php
                                                $mySubmission = ($team->id == $g->team_1_id)
                                                    ? $g->team_1_submission
                                                    : (($team->id == $g->team_2_id) ? $g->team_2_submission : null);
                                                $seed = $mySubmission && preg_match('/^(\d+)-(\d+)$/', $mySubmission, $m) ? $m : null;
                                            @endphp
                                            <x-ui.score-input :team1="$g->team1->name" :team2="$g->team2->name" size="sm"
                                                               :team1Value="$seed[1] ?? null" :team2Value="$seed[2] ?? null"
                                                               :reverse="$team->id == $g->team_2_id" />
                                            <flux:button type="submit" variant="primary" size="sm">{{ __('Submit') }}</flux:button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            {{--Spelers in je team--}}
            <x-ui.card :title="__('Players in your team')">
                @php $teamPlayers = $team->players; @endphp
                @if($teamPlayers->isEmpty())
                    <x-ui.empty-state :message="__('No players in this team.')" />
                @else
                    <ul class="space-y-1.5 text-sm">
                        @foreach($teamPlayers as $p)
                            <li class="flex items-center justify-between">
                                <span>{{ $p->firstName }}</span>
                                <span class="text-xs text-zinc-500">{{ $p->team_code }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            {{--Chat / Posts --}}
            <x-ui.card :title="__('Team posts')">
                {{-- New thread form --}}
                <form method="POST" action="{{ route('team.posts.store') }}" class="mb-4 space-y-3">
                    @csrf
                    <flux:input type="text" name="title" maxlength="120" :placeholder="__('Title')" required />
                    <flux:textarea name="body" rows="3" maxlength="5000" :placeholder="__('Write a message to your team...')" required></flux:textarea>
                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" size="sm">{{ __('Post') }}</flux:button>
                    </div>
                </form>

                @php $threads = $team->postThreads()->with(['author','replies.author'])->orderByDesc('created_at')->take(10)->get(); @endphp
                @if($threads->isEmpty())
                    <x-ui.empty-state :message="__('No posts yet.')" />
                @else
                    <ul class="space-y-4">
                        @foreach($threads as $thread)
                            <li class="rounded-lg border border-neutral-200 dark:border-neutral-700">
                                <div class="border-b border-neutral-200 p-3 dark:border-neutral-700">
                                    <div class="font-medium">{{ $thread->title }}</div>
                                    <div class="whitespace-pre-line text-sm text-zinc-700 dark:text-zinc-300">{{ $thread->body }}</div>
                                    <div class="mt-1 text-xs text-zinc-500">{{ $thread->author?->firstName }} {{ $thread->author?->lastName }}
                                        · {{ $thread->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="space-y-3 p-3">
                                    @forelse($thread->replies as $reply)
                                        <div class="text-sm">
                                            <div class="whitespace-pre-line">{{ $reply->body }}</div>
                                            <div class="mt-0.5 text-xs text-zinc-500">
                                                — {{ $reply->author?->firstName }} {{ $reply->author?->lastName }}
                                                · {{ $reply->created_at->diffForHumans() }}</div>
                                        </div>
                                    @empty
                                        <x-ui.empty-state class="text-xs" :message="__('No replies yet.')" />
                                    @endforelse

                                    <form method="POST" action="{{ route('team.posts.reply', $thread) }}"
                                          class="border-t border-neutral-200 pt-3 dark:border-neutral-700">
                                        @csrf
                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <flux:textarea name="body" rows="2" maxlength="3000" class="flex-1" :placeholder="__('Write a reply...')" required></flux:textarea>
                                            <flux:button type="submit" variant="primary" size="sm" class="self-end sm:self-start">{{ __('Reply') }}</flux:button>
                                        </div>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        @endif

        <div>
            <flux:link :href="route('home')" wire:navigate>{{ __('Back to Leaderboard') }}</flux:link>
        </div>
    </div>
</x-layouts.app.header>
