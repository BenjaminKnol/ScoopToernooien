<x-layouts.app.header>
    @if (session('success'))
        <x-alert type="success">
            {{ session('success') }}
        </x-alert>
    @endif
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl mt-2">
        <div
            class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <form method="POST" action="{{ route('games.store') }}" class="p-6 space-y-4">
                @csrf
                <h2 class="mb-2 text-lg font-semibold">Create Game</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="team_1_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Team 1</label>
                        <select name="team_1_id" id="team_1_id"
                                class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="team_2_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Team 2</label>
                        <select name="team_2_id" id="team_2_id"
                                class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="startTime" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Start Time</label>
                        <input type="text" name="startTime" id="startTime" required
                               value="{{ old('startTime') }}"
                               class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="13:00">
                    </div>
                    <div>
                        <label for="endTime" class="block text-sm font-medium text-gray-700 dark:text-gray-200">End Time</label>
                        <input type="text" name="endTime" id="endTime" required
                               value="{{ old('endTime') }}"
                               class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="13:15">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="field" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Field</label>
                        <input type="number" name="field" id="field" required min="0"
                               value="{{ old('field') }}"
                               class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="outcome" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Outcome</label>
                        <input type="text" name="outcome" id="outcome"
                               value="{{ old('outcome') }}"
                               class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. 2-1 (optional)">
                    </div>
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
                <h2 class="mb-4 text-lg font-semibold">Manage Games</h2>
                <div class="space-y-6">
                    @foreach($games as $game)
                        <div class="space-y-3 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <div class="flex items-center justify-between text-sm">
                                <div>{{ optional($game->team_1()->first())->name ?? ('Team #'.$game->team_1_id) }} vs {{ optional($game->team_2()->first())->name ?? ('Team #'.$game->team_2_id) }}</div>
                                <div class="text-gray-500">{{ $game->startTime }} - {{ $game->endTime }} · Field {{ $game->field + 1 }}</div>
                            </div>
                            <form method="POST" action="{{ route('games.update', $game->id) }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-xs text-gray-600">Team 1</label>
                                    <select name="team_1_id" class="mt-1 block w-full rounded-md border-2 border-gray-300">
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" @selected($team->id === $game->team_1_id)>{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Team 2</label>
                                    <select name="team_2_id" class="mt-1 block w-full rounded-md border-2 border-gray-300">
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" @selected($team->id === $game->team_2_id)>{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Start</label>
                                    <input type="text" name="startTime" value="{{ $game->startTime }}" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">End</label>
                                    <input type="text" name="endTime" value="{{ $game->endTime }}" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Field</label>
                                    <input type="number" name="field" value="{{ $game->field }}" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Outcome</label>
                                    <input type="text" name="outcome" value="{{ $game->outcome }}" placeholder="x-x" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                                </div>
                                <div class="md:col-span-6 flex items-center justify-end gap-2">
                                    <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Save</button>
                                    <button type="submit" form="delete-game-{{ $game->id }}" class="inline-flex justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700" onclick="return confirm('Delete this game?');">Delete</button>
                                </div>
                            </form>
                            <form id="delete-game-{{ $game->id }}" method="POST" action="{{ route('games.destroy', $game->id) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Teams: Create -->
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
            <h2 class="mb-4 text-lg font-semibold">Create Team</h2>
            <form method="POST" action="{{ route('teams.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm">Players</label>
                        <input type="number" name="number_of_players" min="0" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm">Points</label>
                        <input type="number" name="points" min="0" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm">Costume</label>
                        <input type="number" name="costume_rating" min="0" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Create Team</button>
                </div>
            </form>
        </div>

        <!-- Teams: Manage -->
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
            <h2 class="mb-4 text-lg font-semibold">Manage Teams</h2>
            <div class="space-y-4">
                @foreach($teams as $team)
                    <form method="POST" action="{{ route('teams.update', $team->id) }}" class="grid grid-cols-5 gap-3 items-end border-b border-gray-200 pb-3 dark:border-gray-700">
                        @csrf
                        @method('PUT')
                        <div class="col-span-2">
                            <label class="block text-sm">Name</label>
                            <input type="text" name="name" value="{{ $team->name }}" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm">Players</label>
                            <input type="number" name="number_of_players" value="{{ $team->number_of_players }}" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm">Points</label>
                            <input type="number" name="points" value="{{ $team->points }}" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm">Costume</label>
                            <input type="number" name="costume_rating" value="{{ $team->costume_rating }}" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                        </div>
                        <div class="col-span-5 flex justify-end gap-2">
                            <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Save</button>
                            <button type="submit" form="delete-team-{{ $team->id }}" class="inline-flex justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700" onclick="return confirm('Delete this team?');">Delete</button>
                        </div>
                    </form>
                    <form id="delete-team-{{ $team->id }}" method="POST" action="{{ route('teams.destroy', $team->id) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </div>

        <!-- Players: Import & Assign -->
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
            <h2 class="mb-4 text-lg font-semibold">Players</h2>
            <div class="grid gap-6">
                <!-- Import CSV -->
                <form method="POST" action="{{ route('players.import') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Import CSV</label>
                    <input type="file" name="csv" accept=".csv,text/csv" class="mt-1 block w-full rounded-md border-2 border-gray-300" required />
                    <p class="text-xs text-gray-500">Required columns: Voornaam, Achternaam, Email</p>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Upload</button>
                    </div>
                </form>
                <!-- Manually create a player -->
                <p class="block text-sm font-medium text-gray-700 dark:text-gray-200">Manual player creation</p>
                <form method="POST" id="createPlayerManually" action="{{ route('players.store') }}" class="grid grid-cols-3 gap-3 items-end border-gray-200 pb-3 dark:border-gray-700">
                    @csrf
                    <div class="">
                        <label class="block text-sm">{{ __('firstName') }}</label>
                        <input type="text" name="name" value="" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                    <div class="">
                        <label class="block text-sm">{{ __('lastName') }}</label>
                        <input type="text" name="name" value="" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                    <div class="">
                        <label class="block text-sm">E-mail</label>
                        <input type="text" name="name" value="" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                    <div></div>
                    <div></div>
                    <div class="flex justify-end">
                        <button type="submit" form="createPlayerManually" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Create Team</button>
                    </div>
                </form>
                <!-- Assign Players to Teams -->
                <div class="">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 pr-4">Name</th>
                                    <th class="py-2 pr-4">Email</th>
                                    <th class="py-2 pr-4">Team</th>
                                    <th class="py-2 pr-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($players as $player)
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="py-2 pr-4">{{ $player->firstName }} {{ $player->secondName }}</td>
                                        <td class="py-2 pr-4">{{ $player->email }}</td>
                                        <td class="py-2 pr-4">
                                            <form method="POST" action="{{ route('players.update', $player->id) }}" class="flex items-center gap-2 justify-end md:justify-start">
                                                @csrf
                                                @method('PUT')
                                                <select name="team_id" class="mt-1 block rounded-md border-2 border-gray-300">
                                                    <option value="">— Unassigned —</option>
                                                    @foreach($teams as $team)
                                                        <option value="{{ $team->id }}" @selected($player->team_id === $team->id)>{{ $team->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">Save</button>
                                                <button type="submit" class="inline-flex justify-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700" onclick="return confirm('Delete this player?');">Delete</button>
                                            </form>
                                        </td>
                                        <td class="py-2 pr-4 text-right">
                                            @if($player->user)
                                                <span class="text-gray-500">User: {{ $player->user->name }}</span>
                                                <form id="delete-user-{{ $player->id }}" method="POST" action="{{ route('players.destroy', $player->id) }}" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @else
                                                <span class="text-orange-600">No user</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-gray-500">No players yet. Import a CSV to begin.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
</x-layouts.app.header>
