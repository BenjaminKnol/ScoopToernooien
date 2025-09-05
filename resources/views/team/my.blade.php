@php
    use App\Models\Game;
    use Illuminate\Support\Carbon;
@endphp
<x-layouts.app.header>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        @guest()
            <div class="grid auto-rows-min gap-4 grid-cols-1">
                <div>
                    <h1 class="text-2xl font-semibold">My Team</h1>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300 mt-1">This page is only accessible when you
                        are logged in.</p>
                </div>
            </div>
        @endguest

        @if(!$player->team_id)
            <div class="rounded-xl border border-neutral-300 dark:border-neutral-700 p-4 bg-white dark:bg-neutral-800">
                <p class="mb-2">We haven't linked your account to a team yet.</p>
                <p class="text-sm text-neutral-600 dark:text-neutral-300">Ask an administrator to link your account to
                    your team, or provide guidance to implement automatic linking from sign-ups.</p>
            </div>
        @else
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-3 border-b border-neutral-200 dark:border-neutral-700 pb-2">
                <span>{{ $team->name }}</span>
                @if(!empty($team->color_hex) || !empty($team->color_name))
                    <span class="inline-flex items-center gap-2 text-sm font-normal text-zinc-700 dark:text-zinc-300">
                        <span class="inline-block h-4 w-4 rounded-full border border-zinc-300 dark:border-zinc-600"
                              style="background-color: {{ $team->color_hex ?? '#ccc' }}"></span>
                        <span>{{ __('Kleur') }}: {{ $team->color_name ?? __('onbekend') }}</span>
                    </span>
                @endif
            </h1>


            {{--            Upcoming games--}}
            <div class="rounded-xl border border-neutral-300 dark:border-neutral-700 p-4 bg-white dark:bg-neutral-800">
                <div
                    class="mb-2 pb-1 border-b border-neutral-200 dark:border-neutral-700 font-medium">{{ __('Upcoming games') }}</div>
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
                    <div class="text-zinc-500 dark:text-zinc-400">{{ __('No upcoming games') }}</div>
                @endif
            </div>

            {{-- Report past game result --}}
            <div class="rounded-xl border border-neutral-300 dark:border-neutral-700 p-4 bg-white dark:bg-neutral-800">
                <div
                    class="mb-2 pb-1 border-b border-neutral-200 dark:border-neutral-700 font-medium">{{ __('Report result') }}</div>
                @php
                    $pastGames = Game::where(function($q) use($team){ $q->where('team_1_id',$team->id)->orWhere('team_2_id',$team->id); })
                        ->where('start_time','<=', now())
                        ->orderByDesc('start_time')
                        ->take(10)
                        ->get();
                @endphp
                @if($pastGames->isEmpty())
                    <div class="text-zinc-500 dark:text-zinc-400 text-sm">{{ __('No games to report yet.') }}</div>
                @else
                    <ul class="space-y-3">
                        @foreach($pastGames as $g)
                            <li class="border border-neutral-200 dark:border-neutral-700 rounded-md p-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm">
                                        <span class="font-medium">
                                            {{ Carbon::hasFormat($g->start_time, 'H:i') ? $g->start_time : (optional(Carbon::parse($g->start_time, null))->format('H:i') ?? (preg_match('/\\d{2}:\\d{2}/', $g->start_time, $m) ? $m[0] : $g->start_time)) }}
                                        </span>
                                        · {{ $g->opponent($team->id)->name }}
                                        <span
                                            class="text-zinc-500"><br>({{ __('Field :n', ['n' => $g->field + 1]) }})</span>
                                    </div>
                                    @if($g->accepted_outcome)
                                        @php
                                            $outcomes = [$t1Outcome, $t2Outcome] = explode('-', $g->accepted_outcome);
                                            $winner = max($outcomes);
                                            if($t2Outcome == $t1Outcome) {
                                                $colour = "text-gray-500 dark:text-gray-200";
                                            } elseif ($winner == $t1Outcome && $g->team_1_id == $team->id || $winner == $t2Outcome && $g->team_2_id == $team->id) {
                                                $colour = "text-green-700 dark:text-green-400";
                                            } else {
                                                $colour = "text-red-700 dark:text-red-400";
                                            }
                                        @endphp
                                        <div class="text-xs  {{ $colour }}">{{ __('Result') }}
                                            : {{ $g->accepted_outcome }}</div>

                                    @else
                                        <form method="POST" action="{{ route('team.games.report', $g) }}"
                                              class="flex items-center gap-2">
                                            @csrf
                                            <input
                                                type="text"
                                                @if($team->id == $g->team_1_id && $g->team_1_submission) value="{{ $g->team_1_submission }}"
                                                @elseif($team->id == $g->team_2_id && $g->team_2_submission) value="{{ $g->team_2_submission }}"
                                                @endif
                                                inputmode="numeric"
                                                name="score"
                                                placeholder="{{ $g->team1->name }}-{{ $g->team2->name }}"
                                                title="Use the format x-x (e.g., 3-12), positive integers only"
                                                pattern="[1-9]\d*-[1-9]\d*"
                                                required
                                                class="w-32 rounded-md border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-2 py-1 text-sm"
                                            />
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">{{ __('Submit') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{--Spelers in je team--}}
            <div class="rounded-xl border border-neutral-300 dark:border-neutral-700 p-4 bg-white dark:bg-neutral-800">
                <div
                    class="mb-2 pb-1 border-b border-neutral-200 dark:border-neutral-700 font-medium">{{ __('Players in your team') }}</div>
                @php $teamPlayers = $team->players; @endphp
                @if($teamPlayers->isEmpty())
                    <div class="text-zinc-500 dark:text-zinc-400 text-sm">{{ __('No players in this team.') }}</div>
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
            </div>

            {{--Chat / Posts --}}
            <div class="rounded-xl border border-neutral-300 dark:border-neutral-700 p-4 bg-white dark:bg-neutral-800">
                <div
                    class="mb-2 pb-1 border-b border-neutral-200 dark:border-neutral-700 font-medium">{{ __('Team posts') }}</div>

                {{-- New thread form --}}
                <form method="POST" action="{{ route('team.posts.store') }}" class="space-y-2 mb-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-2">
                        <input type="text" name="title" maxlength="120" placeholder="{{ __('Title') }}" required
                               class="block w-full rounded-md border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"/>
                        <textarea name="body" rows="3" maxlength="5000"
                                  placeholder="{{ __('Write a message to your team...') }}" required
                                  class="block w-full rounded-md border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Post') }}</button>
                    </div>
                </form>

                @php $threads = $team->postThreads()->with(['author','replies.author'])->orderByDesc('created_at')->take(10)->get(); @endphp
                @if($threads->isEmpty())
                    <div class="text-zinc-500 dark:text-zinc-400 text-sm">{{ __('No posts yet.') }}</div>
                @else
                    <ul class="space-y-4">
                        @foreach($threads as $thread)
                            <li class="border border-neutral-200 dark:border-neutral-700 rounded-md">
                                <div class="p-3 border-b border-neutral-200 dark:border-neutral-700">
                                    <div class="font-medium">{{ $thread->title }}</div>
                                    <div
                                        class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-line">{{ $thread->body }}</div>
                                    <div
                                        class="mt-1 text-xs text-zinc-500">{{ $thread->author?->firstName }} {{ $thread->author?->lastName }}
                                        · {{ $thread->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="p-3 space-y-3">
                                    @forelse($thread->replies as $reply)
                                        <div class="text-sm">
                                            <div class="whitespace-pre-line">{{ $reply->body }}</div>
                                            <div class="mt-0.5 text-xs text-zinc-500">
                                                — {{ $reply->author?->firstName }} {{ $reply->author?->lastName }}
                                                · {{ $reply->created_at->diffForHumans() }}</div>
                                        </div>
                                    @empty
                                        <div class="text-xs text-zinc-500">{{ __('No replies yet.') }}</div>
                                    @endforelse

                                    <form method="POST" action="{{ route('team.posts.reply', $thread) }}"
                                          class="pt-1 border-t border-neutral-200 dark:border-neutral-700">
                                        @csrf
                                        <div class="mt-2 flex gap-2">
                                            <textarea name="body" rows="2" maxlength="3000"
                                                      placeholder="{{ __('Write a reply...') }}" required
                                                      class="flex-1 rounded-md border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"></textarea>
                                            <button type="submit"
                                                    class="self-start inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Reply') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        @endif

        <div class="mt-4">
            <a href="{{ route('home') }}" class="underline text-blue-600 dark:text-blue-400">Back to Leaderboard</a>
        </div>
    </div>
</x-layouts.app.header>
