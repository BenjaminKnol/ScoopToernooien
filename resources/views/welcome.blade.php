@php
    use Illuminate\Support\Carbon;
@endphp
<x-layouts.app.header>
    <div class="flex w-full flex-col gap-6 p-4">
        <div class="grid auto-rows-min gap-2 grid-cols-1">
            <div class="flex items-end justify-between">
                <div>
                    <flux:heading size="xl" class="font-display">{{ __('Leaderboard') }}</flux:heading>
                    <flux:subheading>{{ __('All teams in one group. Sorted by points.') }}</flux:subheading>
                </div>
            </div>
        </div>

        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($teams as $index => $team)
                <x-ui.card
                    class="group relative border-t-4 transition hover:shadow-md {{ $index === 0 ? 'ring-1 ring-(--color-brand-green)/40' : '' }}"
                    style="border-top-color: {{ $team->color_hex ?? 'var(--color-zinc-300)' }}">
                    <flux:badge class="absolute right-3 top-3" :color="$index === 0 ? 'amber' : 'zinc'" size="sm" variant="pill">
                        #{{ $index + 1 }}
                    </flux:badge>

                    <div class="flex h-full flex-col gap-3">
                        <div class="flex items-center justify-between pr-14">
                            <div class="truncate font-semibold text-zinc-900 dark:text-zinc-50 inline-flex items-center gap-2">
                                <span class="truncate">{{ $team->name }}</span>
                                @if(!empty($team->color_hex))
                                    <span
                                        class="inline-block h-3 w-3 shrink-0 rounded-full border border-zinc-300 dark:border-zinc-600"
                                        title="{{ $team->color_name ?? '' }}"
                                        style="background-color: {{ $team->color_hex }}"></span>
                                @endif
                            </div>
                            <flux:badge color="zinc">{{ $team->points ?? 0 }} {{ __('pts') }}</flux:badge>
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
                                <x-ui.empty-state :message="__('No upcoming games')" />
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    </div>

    <!-- Bottom promo section: centered. Replace src/alt/text as needed. -->
    <div class="mt-6 w-full">
        <div class="mx-auto max-w-3xl">
            <x-ui.card class="flex flex-col items-center gap-3 text-center">
                <span class="truncate text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Upcoming events') }}</span>
                <img
                    class="block w-full max-w-xs rounded-lg"
                    src="{{ Vite::asset('resources/images/upcoming_events/PubquizBorrel.jpeg') }}"
                    alt="Upcoming event promo image"/>
                <div class="space-y-2 text-start sm:text-center">
                    <flux:heading size="lg">Upcoming Event</flux:heading>
                    <div class="space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <p class="font-semibold text-zinc-900 dark:text-zinc-100">Lieve scoopers</p>

                        <p>Wie o wie wordt de winnaar van de avond? 🕵️‍♂️✨ Op 13 mei gaan we los bij de Kurk met de enige
                            echte Wie is het? Pubquiz borrel Verwacht een Pubquiz met veel verschillende rondes die je
                            koppie weer even laten kraken🧠Voor het winnende team ligt er natuurlijk een geweldige
                            prijs klaar! 🏆</p>

                        <p>Maar er is meer... Het A-team en de PromoCo onthullen deze avond namelijk zowel het thema
                            van het Eindfeest als het thema van de OWee! 🎊🎈 Dubbel feest en dubbel zoveel reden om
                            erbij te zijn. Vergeet ook niet direct 27 juni in je agenda te blokken, want dan knallen
                            we het jaar uit tijdens het Eindfeest. 🗓️</p>

                        <p>We zien jullie om 21:00 uur @Kurk. Zet je speurneus op en zorg dat je deze onthullingen
                            niet mist! 🔍</p>

                        <p>Liefs,<br>Het A-team💚</p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app.header>
