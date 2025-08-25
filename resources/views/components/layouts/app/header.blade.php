<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a href="{{ route('home') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="trophy" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
                    {{ __('Leaderboard') }}
                </flux:navbar.item>
                @auth
                <flux:navbar.item icon="user-group" :href="route('my-team')" :current="request()->routeIs('my-team')" wire:navigate>
                    {{ __('My Team') }}
                </flux:navbar.item>
                @endauth
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
            </flux:navbar>

            <!-- Desktop User Menu -->
            @auth
            <flux:dropdown position="top" align="end">
                <flux:profile
                    class="cursor-pointer"
                />

                <flux:menu>

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('my-team')" icon="user-group" wire:navigate>{{ __('My Team') }}</flux:menu.item>
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
            @else
                <flux:navbar class="-mb-px max-lg:hidden">
                    <flux:navbar.item icon="arrow-right-start-on-rectangle" :href="route('login')" :current="request()->routeIs('login')" wire:navigate>
                        {{ __('Login') }}
                    </flux:navbar.item>
                </flux:navbar>
            @endauth
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar stashable sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('home') }}" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Navigation')">
                    <flux:navlist.item icon="trophy" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
                        {{ __('Leaderboard') }}
                    </flux:navlist.item>
                    @auth
                    <flux:navlist.item icon="user-group" :href="route('my-team')" :current="request()->routeIs('my-team')" wire:navigate>
                        {{ __('My Team') }}
                    </flux:navlist.item>
                    @endauth
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                @auth
                <flux:navlist.group :heading="__('Account')">
                    <flux:navlist.item icon="cog" :href="route('settings.profile')" :current="request()->routeIs('settings.*')" wire:navigate>
                        {{ __('Settings') }}
                    </flux:navlist.item>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:navlist.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:navlist.item>
                    </form>
                </flux:navlist.group>
                @else
                <flux:navlist.group :heading="__('Account')">
                    <flux:navlist.item icon="arrow-right-start-on-rectangle" :href="route('login')" :current="request()->routeIs('login')" wire:navigate>
                        {{ __('Login') }}
                    </flux:navlist.item>
                </flux:navlist.group>
                @endauth
            </flux:navlist>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
