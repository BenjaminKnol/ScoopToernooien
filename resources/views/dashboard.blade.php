<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern
                    class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern
                    class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern
                    class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
            </div>
        </div>
        <div
            class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <form method="POST" action="{{ route('games.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="team_1_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Team
                            1</label>
                        <select name="team_1_id" id="team_1_id"
                                class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name . " in poule " .$team->poule}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="team_2_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Team
                            2</label>
                        <select name="team_2_id" id="team_2_id"
                                class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name . " in poule " . $team->poule}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="startTime" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Start
                            Time</label>
                        <input type="text" name="startTime" id="startTime"
                               class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="endTime" class="block text-sm font-medium text-gray-700 dark:text-gray-200">End
                            Time</label>
                        <input type="text" name="endTime" id="endTime"
                               class="mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label for="outcome"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-200">Outcome</label>
                    <input type="text" name="outcome" id="outcome"
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
    </div>
</x-layouts.app>
