<flux:modal name="confirm-score" class="w-full max-w-md">
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('Confirm score update') }}</flux:heading>
            <flux:subheading id="confirm-score-teams"></flux:subheading>
        </div>

        <div class="space-y-1.5 rounded-lg bg-zinc-50 p-3 text-sm dark:bg-zinc-800">
            <div class="flex items-center justify-between gap-3">
                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Current final') }}</span>
                <span id="confirm-score-current" class="font-medium"></span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span id="confirm-score-team1-label" class="text-zinc-500 dark:text-zinc-400"></span>
                <span id="confirm-score-team1" class="font-medium"></span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span id="confirm-score-team2-label" class="text-zinc-500 dark:text-zinc-400"></span>
                <span id="confirm-score-team2" class="font-medium"></span>
            </div>
            <div class="mt-1.5 flex items-center justify-between gap-3 border-t border-zinc-200 pt-1.5 dark:border-zinc-700">
                <span class="text-zinc-500 dark:text-zinc-400">{{ __('New final score') }}</span>
                <span id="confirm-score-new" class="font-semibold"></span>
            </div>
        </div>

        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This will accept the result, award points, and record your admin account.') }}</p>

        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" type="button" x-on:click="$flux.modal('confirm-score').close()">{{ __('Cancel') }}</flux:button>
            <flux:button id="confirm-score-submit" variant="primary" type="button">{{ __('Confirm') }}</flux:button>
        </div>
    </div>
</flux:modal>
