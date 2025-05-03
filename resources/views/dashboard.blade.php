<x-layouts.app :title="__('Dashboard')">
    @if (session('success'))
        <x-alert type="success">
            {{ session('success') }}
        </x-alert>
    @endif
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <h2 class="mb-4 text-lg font-semibold">Update Game Outcomes</h2>
                <div class="space-y-4">
                    @foreach($games->filter(function($game) {
                        return is_null($game->outcome) &&
                               \Carbon\Carbon::parse($game->endTime)->gt(\Carbon\Carbon::now()->subHour()) &&
                               \Carbon\Carbon::parse($game->endTime)->lt(\Carbon\Carbon::now());
                    }) as $game)
                        <form method="POST" action="{{ route('games.update', $game->id) }}"
                              class="space-y-2 border-b border-gray-200 pb-4 dark:border-gray-700">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm">{{ $game->team_1()->first()->name }} vs {{ $game->team_2()->first()->name }}</span>
                                <span class="text-sm text-gray-500">{{ $game->startTime }}</span>
                            </div>
                            <div class="flex gap-4">
                                <input type="text" name="outcome" value="{{ old('outcome') }}"
                                       placeholder="enter the outcome as follows: x-x"
                                       required
                                       class="w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="submit"
                                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Update
                                </button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <h2 class="mb-4 text-lg font-semibold">Recently Updated Games</h2>
                <div class="space-y-4">
                    @foreach($games->filter(function($game) {
                        return !is_null($game->outcome) &&
                               $game->updated_at->gt(\Carbon\Carbon::now()->subMinutes(10));
                    }) as $game)
                        <form method="POST" action="{{ route('games.update', $game) }}"
                              class="space-y-2 border-b border-gray-200 pb-4 dark:border-gray-700">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm">{{ $game->team_1()->first()->name }} vs {{ $game->team_2()->first()->name }}</span>
                                <span class="text-sm text-gray-500">{{ $game->startTime }}</span>
                            </div>
                            <div class="flex gap-4">
                                <input type="text" name="outcome" value="{{ old('outcome') }}"
                                       placeholder="enter the outcome as follows: x-x"
                                       required
                                       class="w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="submit"
                                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Update
                                </button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-red-500 p-4">
                <h2 class="mb-4 text-xl text-center font-bold text-white">⚠️ DANGER: Split Poules Operation ⚠️</h2>
                <p class="mb-4 text-white text-center font-semibold">Dit is niet makkelijk te herstellen niet
                    drukken!!!</p>
                <form method="POST" action="{{ route('splitpoules') }}" class="space-y-4">
                    @csrf
                    <div class="flex flex-col items-center gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="confirm" name="confirm" required
                                   class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <label for="confirm" class="ml-2 text-white font-medium">I understand this action cannot be
                                undone</label>
                        </div>
                        <button type="submit"
                                class="animate-pulse inline-flex justify-center rounded-md border-4 border-yellow-300 border-dashed bg-yellow-500 py-3 px-6 text-lg font-bold text-black shadow-lg hover:bg-yellow-600 focus:outline-none focus:ring-4 focus:ring-red-700 focus:ring-offset-2">
                            ⚠️ SPLIT POULES ⚠️
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div
            class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <form method="POST" action="{{ route('games.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="team_1_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Team
                            1</label>
                        <select name="team_1_id" id="team_1_id"
                                class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($teams as $team)
                                <option
                                    value="{{ old('team_1_id') ?? $team->id }}">{{ $team->name . " in poule " .$team->poule}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="team_2_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Team
                            2</label>
                        <select name="team_2_id" id="team_2_id"
                                class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($teams as $team)
                                <option
                                    value="{{ old('team_2_id') ?? $team->id }}">{{ $team->name . " in poule " . $team->poule}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="startTime" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Start
                            Time</label>
                        <input type="text" name="startTime" id="startTime" required
                               value="{{ old('startTime') }}"
                               class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="endTime" class="block text-sm font-medium text-gray-700 dark:text-gray-200">End
                            Time</label>
                        <input type="text" name="endTime" id="endTime" required
                               value="{{ old('endTime') }}"
                               class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label for="field" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Field</label>
                    <input type="text" name="field" id="field" required
                           value="{{ old('field') }}"
                           class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="outcome"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-200">Outcome</label>
                    <input type="text" name="outcome" id="outcome" required
                           value="{{ old('outcome') }}"
                           class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Create Game
                    </button>
                </div>
            </form>
        </div>
        <div
            class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <h2 class="mb-4 text-lg font-semibold">Update Game Outcomes</h2>
                <div class="space-y-4">
                    @foreach($games->filter(function($game) {return is_null($game->outcome);}) as $game)
                        <form method="POST" action="{{ route('games.update', $game->id) }}"
                              class="space-y-2 border-b border-gray-200 pb-4 dark:border-gray-700">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm">{{ $game->team_1()->first()->name }} vs {{ $game->team_2()->first()->name }}</span>
                                <span class="text-sm text-gray-500">{{ $game->startTime }}</span>
                            </div>
                            <div class="flex gap-4">
                                <input type="text" name="outcome" value="{{ old('outcome') }}"
                                       placeholder="enter the outcome as follows: x-x"
                                       required
                                       class="w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="submit"
                                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Update
                                </button>
                            </div>
                        </form>
                    @endforeach
                </div>
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
</x-layouts.app>
