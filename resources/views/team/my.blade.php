<x-layouts.app.header>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="grid auto-rows-min gap-4 grid-cols-1">
            <div>
                <h1 class="text-2xl font-semibold">My Team</h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-300 mt-1">This page is only accessible when you are logged in.</p>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-300 dark:border-neutral-700 p-4 bg-white dark:bg-neutral-800">
            <p class="mb-2">We haven't linked your account to a team yet.</p>
            <p class="text-sm text-neutral-600 dark:text-neutral-300">Ask an administrator to link your account to your team, or provide guidance to implement automatic linking from sign-ups.</p>
        </div>

        <div class="mt-4">
            <a href="{{ route('home') }}" class="underline text-blue-600 dark:text-blue-400">Back to Leaderboard</a>
        </div>
    </div>
</x-layouts.app.header>
