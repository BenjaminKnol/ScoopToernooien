<x-layouts.app.header>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div class="grid auto-rows-min gap-2 grid-cols-1">
            <div class="flex items-end justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Leaderboard') }}</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('All teams in one group. Sorted by points.') }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($teams as $index => $team)
                <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="absolute right-3 top-3 inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        #{{ $index + 1 }}
                    </div>
                    <div class="flex h-full flex-col gap-3 p-4">
                        <div class="flex items-center justify-between">
                            <div class="truncate font-semibold text-zinc-900 dark:text-zinc-50">{{ $team->name }}</div>
                            <div class="mr-16 inline-flex items-center rounded-lg bg-zinc-100 px-2 py-1 text-sm font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ $team->points ?? 0 }} {{ __('pts') }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 p-3 text-xs text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                            <div class="mb-1 font-medium text-zinc-900 dark:text-zinc-100">{{ __('Upcoming') }}</div>
                            @php $games = $team->upcomingGames()->take(3); @endphp
                            @if($games->count() > 0)
                                <ul class="space-y-1.5">
                                    @foreach($games as $game)
                                        <li class="flex items-center justify-between">
                                            <span class="truncate">{{ $game->start_time }} · {{ $game->opponent($team->id)->name }}</span>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Field :n', ['n' => $game->field]) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('No upcoming games') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app.header>
