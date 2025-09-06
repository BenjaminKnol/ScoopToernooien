@php
    use Illuminate\Support\Carbon;
@endphp
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
                <div
                    class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                    <div
                        class="absolute right-3 top-3 inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        #{{ $index + 1 }}
                    </div>
                    <div class="flex h-full flex-col gap-3 p-4">
                        <div class="flex items-center justify-between">
                            <div
                                class="truncate font-semibold text-zinc-900 dark:text-zinc-50 inline-flex items-center gap-2">
                                <span class="truncate">{{ $team->name }}</span>
                                @if(!empty($team->color_hex))
                                    <span
                                        class="inline-block h-3 w-3 rounded-full border border-zinc-300 dark:border-zinc-600"
                                        title="{{ $team->color_name ?? '' }}"
                                        style="background-color: {{ $team->color_hex }}"></span>
                                @endif
                            </div>
                            <div
                                class="mr-16 inline-flex items-center rounded-lg bg-zinc-100 px-2 py-1 text-sm font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ $team->points ?? 0 }} {{ __('pts') }}
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-zinc-200 p-3 text-xs text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                            <div class="mb-1 font-medium text-zinc-900 dark:text-zinc-100">{{ __('Upcoming') }}</div>
                            @php $games = $team->upcomingGames()->take(3); @endphp
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
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <!-- Bottom promo section: centered. Mobile shows an image. Replace src/alt/text as needed. -->
    <div class="mt-6 w-full">
        <div class="mx-auto max-w-3xl">
            <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-zinc-200 bg-white p-4 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Image: visible on mobile (sm:hidden), hidden on >=sm -->
                <span class="truncate">{{ __('Upcoming events') }}</span>
                <img
                    class="block w-full max-w-xs rounded-lg sm:hidden"
                    src="{{ Vite::asset('resources/images/upcoming_events/scoopweekend.jpeg') }}"
                    alt="Upcoming event promo image" />
                <!-- Small text to advertise upcoming event -->
                <div class="space-y-1">
                    <div class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        <!-- Edit this headline -->
                        Upcoming Event
                    </div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">
                        <!-- Edit this description -->
                        <h1>💚Lieve scoopers💚</h1>

                        <p>Afgelopen zaterdag hebben we het seizoen goed samen afgesloten tijdens het eindfeest🎉. Maarr niet getreurd…. Want het nieuwe seizoen komt er alweer snel aan en we starten traditiegetrouw met het Scoopweekend!!! 🏕️</p>

                        <p>De Tripcie is alweer druk bezig geweest met de voorbereidingen. Of je nou net nieuw bent bij scoop of er al jaren bij zit iedereen is welkom om samen het nieuwe seizoen af te trappen en elkaar beter te leren kennen. 🤗</p>

                        <p>Het thema houden we nog even geheim, maar zodra het seizoen weer begint, hoor je snel meer👀. Het word een weekend om nooit meer te vergeten, of toch liever wel😉.</p>

                        <p>Dus zet het in je agenda: 17-19 oktober. En dan zien we jullie dan!! 🥳</p>

                        <p>Liefsss,
                        Tripcie❤️</p>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app.header>
