@props([
    'team1',
    'team2',
    'team1Value' => null,
    'team2Value' => null,
    'size' => null,
    'reverse' => false,
])

<div {{ $attributes->class('flex items-end gap-2') }}>
    <flux:input type="number" min="0" name="team_1_score" :size="$size" :label="$team1" :value="$team1Value"
                class="w-full" field:class="{{ $reverse ? 'order-3' : 'order-1' }}" required />
    <span class="order-2 pb-2.5 text-zinc-400 dark:text-zinc-500">–</span>
    <flux:input type="number" min="0" name="team_2_score" :size="$size" :label="$team2" :value="$team2Value"
                class="w-full" field:class="{{ $reverse ? 'order-1' : 'order-3' }}" required />
</div>
