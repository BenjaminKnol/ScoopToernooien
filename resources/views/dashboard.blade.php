<x-layouts.admin>
    <div class="space-y-6">
        @if (session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        <div>
            <flux:heading size="xl" class="font-display">{{ __('Dashboard') }}</flux:heading>
            <flux:subheading>{{ __('An overview of the tournament. Jump into a section to manage it.') }}</flux:subheading>
        </div>

        @if($conflictsCount > 0)
            <a href="{{ route('games.index') }}#conflicts" wire:navigate
               class="block rounded-xl border border-amber-300 bg-amber-50 p-4 transition hover:border-amber-400 dark:border-amber-400/40 dark:bg-amber-400/10">
                <div class="flex items-center gap-3">
                    <flux:icon icon="exclamation-triangle" class="size-6 shrink-0 text-amber-600 dark:text-amber-400" />
                    <div>
                        <div class="font-semibold text-amber-900 dark:text-amber-200">{{ __('Attention needed') }}</div>
                        <div class="text-sm text-amber-700 dark:text-amber-300">{{ $conflictsCount }} {{ __('conflicting game') }} {{ \Illuminate\Support\Str::plural(__('result'), $conflictsCount) }} {{ __('require admin action.') }}</div>
                    </div>
                </div>
            </a>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('teams.index') }}" wire:navigate class="block">
                <x-ui.card class="h-full transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Teams') }}</div>
                            <flux:heading size="xl" class="font-display">{{ $teamsCount }}</flux:heading>
                        </div>
                        <flux:icon icon="user-group" class="size-8 text-(--color-brand-green)" />
                    </div>
                </x-ui.card>
            </a>

            <a href="{{ route('games.index') }}" wire:navigate class="block">
                <x-ui.card class="h-full transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Games') }}</div>
                            <flux:heading size="xl" class="font-display">{{ $gamesCount }}</flux:heading>
                        </div>
                        <flux:icon icon="flag" class="size-8 text-(--color-brand-green)" />
                    </div>
                </x-ui.card>
            </a>

            <a href="{{ route('players.index') }}" wire:navigate class="block">
                <x-ui.card class="h-full transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Players') }}</div>
                            <flux:heading size="xl" class="font-display">{{ $playersCount }}</flux:heading>
                        </div>
                        <flux:icon icon="users" class="size-8 text-(--color-brand-green)" />
                    </div>
                </x-ui.card>
            </a>
        </div>

        <x-ui.card :title="__('Quick links')">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <flux:button as="a" :href="route('dashboard.generateSchedule')" variant="outline" icon="sparkles" class="justify-start">{{ __('Generate schedule') }}</flux:button>
                <flux:button as="a" :href="route('dashboard.schedule')" variant="outline" icon="calendar-days" class="justify-start">{{ __('View schedule') }}</flux:button>
                <flux:button as="a" :href="route('dashboard.autoAssignTeams')" variant="outline" icon="arrow-path-rounded-square" class="justify-start">{{ __('Auto-assign teams') }}</flux:button>
                <flux:button as="a" :href="route('tournament-settings.edit')" variant="outline" icon="adjustments-horizontal" class="justify-start">{{ __('Tournament settings') }}</flux:button>
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
