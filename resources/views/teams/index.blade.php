<x-layouts.admin>
    <div data-admin class="space-y-6">
        @if (session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        <div>
            <flux:heading size="xl" class="font-display">{{ __('Teams') }}</flux:heading>
            <flux:subheading>{{ __('Create teams and manage rosters.') }}</flux:subheading>
        </div>

        <x-ui.card>
            <form method="POST" action="{{ route('teams.store') }}" class="mb-6 space-y-3">
                @csrf
                <flux:input name="name" :label="__('Name')" required />
                <div class="grid grid-cols-2 gap-3">
                    <flux:input type="number" name="points" :label="__('Points')" min="0" />
                    <flux:input type="number" name="costume_rating" :label="__('Costume')" min="0" />
                </div>
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">{{ __('Create Team') }}</flux:button>
                </div>
            </form>

            <flux:separator class="mb-6" />

            <flux:subheading class="mb-4">{{ __('Manage Teams') }}</flux:subheading>
            <div class="space-y-6">
                @foreach($teams as $team)
                    <div class="border-b border-zinc-200 pb-4 last:border-b-0 last:pb-0 dark:border-zinc-700" data-team-row>
                        <form method="POST" action="{{ route('teams.update', $team->id) }}"
                              class="grid grid-cols-1 items-end gap-3 sm:grid-cols-5"
                              data-ajax="save">
                            @csrf
                            @method('PUT')
                            <div class="sm:col-span-2">
                                @if(!empty($team->color_hex) || !empty($team->color_name))
                                    <div class="mb-1 inline-flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        <span class="inline-block h-3 w-3 rounded-full border border-zinc-300 dark:border-zinc-600"
                                              style="background-color: {{ $team->color_hex ?? '#ccc' }}"></span>
                                        <span>{{ $team->color_name ?? '' }} @if(!empty($team->color_hex))({{ $team->color_hex }})@endif</span>
                                    </div>
                                @endif
                                <flux:input name="name" :label="__('Name')" value="{{ $team->name }}" required />
                            </div>
                            <flux:input type="number" name="points" :label="__('Points')" value="{{ $team->points }}" />
                            <flux:input type="number" name="costume_rating" :label="__('Costume')" value="{{ $team->costume_rating }}" />
                            <div class="flex justify-end gap-2 sm:col-span-5">
                                <flux:button type="submit" variant="primary" size="sm">{{ __('Save') }}</flux:button>
                                <flux:button type="submit" form="delete-team-{{ $team->id }}" variant="danger" size="sm"
                                             onclick="return confirm('{{ __('Delete this team?') }}');">{{ __('Delete') }}</flux:button>
                            </div>
                        </form>
                        <form id="delete-team-{{ $team->id }}" method="POST" action="{{ route('teams.destroy', $team->id) }}"
                              class="hidden" data-ajax="delete" data-remove-target="closest:[data-team-row]">
                            @csrf
                            @method('DELETE')
                        </form>

                        <div class="mt-3">
                            <div class="mb-1 text-sm font-medium">{{ __('Players in this team') }}</div>
                            @php $teamPlayers = $team->players; @endphp
                            @if($teamPlayers->isEmpty())
                                <x-ui.empty-state :message="__('No players in this team.')" />
                            @else
                                <ul class="space-y-1 text-sm">
                                    @foreach($teamPlayers as $p)
                                        <li class="flex items-center justify-between gap-2">
                                            <span>{{ $p->firstName }} {{ $p->lastName }} <span class="text-xs text-zinc-500">({{ $p->email }})</span></span>
                                            <form method="POST" action="{{ route('players.update', $p->id) }}" data-ajax="save">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="team_id" value="">
                                                <flux:button type="submit" variant="danger" size="xs"
                                                             onclick="return confirm('{{ __('Remove this player from the team?') }}');">{{ __('Remove') }}</flux:button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
