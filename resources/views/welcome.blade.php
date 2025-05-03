<x-layouts.app.header>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 grid-cols-1">
            <div>
                <h1 class="">Poule A</h1>
            </div>
        </div>
        <div class="grid auto-rows-min gap-4 grid-cols-4">
            @foreach ($A as $team)
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-neutral-400 bg-slate-200 dark:border-neutral-500 dark:bg-neutral-700">
                    <div class="grid h-full w-full grid-cols-2 grid-rows-2 gap-2 p-2">
                        <div class="flex items-center justify-center font-semibold">{{ $team->name }}</div>
                        <div class="flex items-center justify-center">{{ $team->points ?? 0 }}</div>
                        <div class="grid grid-cols-2 items-center justify-center">
                            @if($team->upcomingGames())
                                @foreach($team->upcomingGames() as $game)
                                    <div
                                        class="text-xs">{{ $game->startTime }}  {{ $game->opponent($team->id)->name . " veld: " . $game->field+1}}</div>
                                @endforeach
                            @endif
                        </div>
                        <div class="flex items-center justify-center">
                            <x-placeholder-pattern class="size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="grid auto-rows-min gap-4 grid-cols-1">
            <div>
                <h1 class="">Poule B</h1>
            </div>
        </div>
        <div class="grid auto-rows-min gap-4 grid-cols-4">
            @foreach ($B as $team)
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-neutral-400 bg-slate-200 dark:border-neutral-500 dark:bg-neutral-700">
                    <div class="grid h-full w-full grid-cols-2 grid-rows-2 gap-2 p-2">
                        <div class="flex items-center justify-center font-semibold">{{ $team->name }}</div>
                        <div class="flex items-center justify-center">{{ $team->points ?? 0 }}</div>
                        <div class="grid grid-cols-2 items-center justify-center">
                            @if($team->upcomingGames())
                                @foreach($team->upcomingGames() as $game)
                                    <div
                                        class="text-xs">{{ $game->startTime }}  {{ $game->opponent($team->id)->name . " veld: " . $game->field+1}}</div>
                                @endforeach
                            @endif
                        </div>
                        <div class="flex items-center justify-center">
                            <x-placeholder-pattern class="size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="grid auto-rows-min gap-4 grid-cols-1">
            <div>
                <h1 class="">Poule C</h1>
            </div>
        </div>
        <div class="grid auto-rows-min gap-4 grid-cols-4">
            @foreach ($C as $team)
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-neutral-400 bg-slate-200 dark:border-neutral-500 dark:bg-neutral-700">
                    <div class="grid h-full w-full grid-cols-2 grid-rows-2 gap-2 p-2">
                        <div class="flex items-center justify-center font-semibold">{{ $team->name }}</div>
                        <div class="flex items-center justify-center">{{ $team->points ?? 0 }}</div>
                        <div class="grid grid-cols-2 items-center justify-center">
                            @if($team->upcomingGames())
                                @foreach($team->upcomingGames() as $game)
                                    <div
                                        class="text-xs">{{ $game->startTime }}  {{ $game->opponent($team->id)->name . " veld: " . $game->field+1}}</div>
                                @endforeach
                            @endif
                        </div>
                        <div class="flex items-center justify-center">
                            <x-placeholder-pattern class="size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="grid auto-rows-min gap-4 grid-cols-1">
            <div>
                <h1 class="">Poule D</h1>
            </div>
        </div>
        <div class="grid auto-rows-min gap-4 grid-cols-4">
            @foreach ($D as $team)
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-neutral-400 bg-slate-200 dark:border-neutral-500 dark:bg-neutral-700">
                    <div class="grid h-full w-full grid-cols-2 grid-rows-2 gap-2 p-2">
                        <div class="flex items-center justify-center font-semibold">{{ $team->name }}</div>
                        <div class="flex items-center justify-center">{{ $team->points ?? 0 }}</div>
                        <div class="grid grid-cols-2 items-center justify-center">
                            @if($team->upcomingGames())
                                @foreach($team->upcomingGames() as $game)
                                    <div
                                        class="text-xs">{{ $game->startTime }}  {{ $game->opponent($team->id)->name . " veld: " . $game->field+1}}</div>
                                @endforeach
                            @endif
                        </div>
                        <div class="flex items-center justify-center">
                            <x-placeholder-pattern class="size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if($Winnaars->isNotEmpty() && $Verliezers->isNotEmpty())
            <div class="grid auto-rows-min gap-4 grid-cols-1">
                <div>
                    <h1 class="">Winnaarspoule</h1>
                </div>
            </div>
            <div class="grid auto-rows-min gap-4 grid-cols-4">
                @foreach ($Winnaars as $team)
                    <div
                        class="relative aspect-video overflow-hidden rounded-xl border border-neutral-400 bg-slate-200 dark:border-neutral-500 dark:bg-neutral-700">
                        <div class="grid h-full w-full grid-cols-2 grid-rows-2 gap-2 p-2">
                            <div class="flex items-center justify-center font-semibold">{{ $team->name }}</div>
                            <div class="flex items-center justify-center">{{ $team->points ?? 0 }}</div>
                            <div class="grid grid-cols-2 items-center justify-center">
                                @if($team->upcomingGames())
                                    @foreach($team->upcomingGames() as $game)
                                        <div
                                            class="text-xs">{{ $game->startTime }}  {{ $game->opponent($team->id)->name . " veld: " . $game->field+1}}</div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="flex items-center justify-center">
                                <x-placeholder-pattern class="size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="grid auto-rows-min gap-4 grid-cols-1">
                <div>
                    <h1 class="">Verliezerspoule</h1>
                </div>
            </div>
            <div class="grid auto-rows-min gap-4 grid-cols-4">
                @foreach ($Verliezers as $team)
                    <div
                        class="relative aspect-video overflow-hidden rounded-xl border border-neutral-400 bg-slate-200 dark:border-neutral-500 dark:bg-neutral-700">
                        <div class="grid h-full w-full grid-cols-2 grid-rows-2 gap-2 p-2">
                            <div class="flex items-center justify-center font-semibold">{{ $team->name }}</div>
                            <div class="flex items-center justify-center">{{ $team->points ?? 0 }}</div>
                            <div class="grid grid-cols-2 items-center justify-center">
                                @if($team->upcomingGames())
                                    @foreach($team->upcomingGames() as $game)
                                        <div
                                            class="text-xs">{{ $game->startTime }}  {{ $game->opponent($team->id)->name }}</div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="flex items-center justify-center">
                                <x-placeholder-pattern class="size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app.header>
