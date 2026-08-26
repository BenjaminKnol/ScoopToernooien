<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-dvh bg-(--color-brand-cream) dark:bg-zinc-900">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-950">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('home') }}" class="relative -mx-4 -mt-4 mb-2 flex items-center gap-2 overflow-hidden border-b-2 border-(--color-brand-green) bg-zinc-900 px-4 py-4" wire:navigate>
                <img src="{{ Vite::asset('resources/images/logo/SHCScoopLogoDarkMode.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto" />
                <span class="truncate font-display text-sm font-semibold text-white">{{ config('app.name') }}</span>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Admin')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="flag" :href="route('games.index')" :current="request()->routeIs('games.*')" wire:navigate>{{ __('Games') }}</flux:navlist.item>
                    <flux:navlist.item icon="user-group" :href="route('teams.index')" :current="request()->routeIs('teams.*')" wire:navigate>{{ __('Teams') }}</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('players.index')" :current="request()->routeIs('players.*')" wire:navigate>{{ __('Players') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Schedule')" class="grid">
                    <flux:navlist.item icon="calendar-days" :href="route('dashboard.schedule')" :current="request()->routeIs('dashboard.schedule')" wire:navigate>{{ __('View schedule') }}</flux:navlist.item>
                    <flux:navlist.item icon="sparkles" :href="route('dashboard.generateSchedule')" :current="request()->routeIs('dashboard.generateSchedule')" wire:navigate>{{ __('Generate schedule') }}</flux:navlist.item>
                    <flux:navlist.item icon="arrow-path-rounded-square" :href="route('dashboard.autoAssignTeams')" :current="request()->routeIs('dashboard.autoAssignTeams')" wire:navigate>{{ __('Auto-assign teams') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Settings')" class="grid">
                    <flux:navlist.item icon="adjustments-horizontal" :href="route('tournament-settings.edit')" :current="request()->routeIs('tournament-settings.*')" wire:navigate>{{ __('Tournament settings') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="trophy" :href="route('home')" wire:navigate>{{ __('Leaderboard') }}</flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile top bar -->
        <flux:header class="border-b border-zinc-200 bg-white lg:hidden dark:border-zinc-700 dark:bg-zinc-950">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <flux:main container>
            {{ $slot }}
        </flux:main>

        @fluxScripts
    </body>
</html>
