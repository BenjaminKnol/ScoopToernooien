<x-layouts.admin>
    <div data-admin class="space-y-6">
        @if (session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="xl" class="font-display">{{ __('Players') }}</flux:heading>
                <flux:subheading>{{ __('Import, create, and assign players to teams.') }}</flux:subheading>
            </div>
            <flux:link :href="route('dashboard.autoAssignTeams')" wire:navigate>{{ __('Auto-assign teams (preview)') }}</flux:link>
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

        <x-ui.card>
            <div class="space-y-6">
                <form method="POST" action="{{ route('players.import') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <flux:input type="file" name="csv" accept=".csv,text/csv" :label="__('Import CSV')"
                                :description="__('Required columns: Voornaam, Achternaam, Email. Optional: Team (e.g., H1 or D3) — gender will be inferred from Team.')"
                                required />
                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary">{{ __('Upload') }}</flux:button>
                    </div>
                </form>

                <flux:separator />

                <div>
                    <flux:subheading class="mb-3">{{ __('Manual player creation') }}</flux:subheading>
                    <form method="POST" id="createPlayerManually" action="{{ route('players.store') }}"
                          class="grid grid-cols-1 items-end gap-3 sm:grid-cols-2 lg:grid-cols-6">
                        @csrf
                        <flux:input name="firstName" :label="__('firstName')" value="{{ old('firstName') }}" required />
                        <flux:input name="lastName" :label="__('lastName')" value="{{ old('lastName') }}" required />
                        <flux:input name="email" :label="__('E-mail')" value="{{ old('email') }}" required />
                        <flux:select name="team_id" :label="__('Team')">
                            <flux:select.option value="">{{ __('— Unassigned —') }}</flux:select.option>
                            @foreach($teams as $team)
                                <flux:select.option value="{{ $team->id }}">{{ $team->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select name="gender" :label="__('Gender')">
                            <flux:select.option value="">{{ __('— Unknown —') }}</flux:select.option>
                            <flux:select.option value="H">H ({{ __('Male') }})</flux:select.option>
                            <flux:select.option value="D">D ({{ __('Female') }})</flux:select.option>
                        </flux:select>
                        <flux:input name="team_code" :label="__('Team code')" value="{{ old('team_code') }}" placeholder="e.g. H1 or D3" />
                        <div class="flex justify-end sm:col-span-2 lg:col-span-6">
                            <flux:button type="submit" form="createPlayerManually" variant="primary">{{ __('Create Player') }}</flux:button>
                        </div>
                    </form>
                </div>

                <x-ui.data-table>
                    <x-slot:head>
                        <th class="px-3 py-2">{{ __('Name') }}</th>
                        <th class="px-3 py-2">{{ __('Email') }}</th>
                        <th class="px-3 py-2">{{ __('Gender') }}</th>
                        <th class="px-3 py-2">{{ __('Team code') }}</th>
                        <th class="px-3 py-2">{{ __('Team') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Actions') }}</th>
                    </x-slot:head>
                    @forelse($players as $player)
                        <tr data-player-row>
                            <td class="px-3 py-2">{{ $player->firstName }} {{ $player->lastName }}</td>
                            <td class="px-3 py-2">{{ $player->email }}</td>
                            <td class="px-3 py-2">{{ $player->gender ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $player->team_code ?? '—' }}</td>
                            <td class="px-3 py-2">
                                <form method="POST" action="{{ route('players.update', $player->id) }}"
                                      class="flex items-center justify-end gap-2 md:justify-start" data-ajax="save">
                                    @csrf
                                    @method('PUT')
                                    <flux:select name="team_id" size="sm" data-autosave>
                                        <flux:select.option value="">{{ __('— Unassigned —') }}</flux:select.option>
                                        @foreach($teams as $team)
                                            <flux:select.option value="{{ $team->id }}" :selected="$player->team_id === $team->id">{{ $team->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:button type="submit" variant="primary" size="xs" class="hidden md:inline-flex">{{ __('Save') }}</flux:button>
                                    @if($player->user)
                                        <flux:button type="submit" form="delete-user-{{ $player->id }}" variant="danger" size="xs"
                                                     onclick="return confirm('{{ __('Delete this player?') }}');">{{ __('Delete') }}</flux:button>
                                    @endif
                                </form>
                            </td>
                            <td class="px-3 py-2 text-right">
                                @if($player->user)
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('User') }}: {{ $player->user->name }}</span>
                                    <form id="delete-user-{{ $player->id }}" method="POST" action="{{ route('players.destroy', $player->id) }}"
                                          class="hidden" data-ajax="delete" data-remove-target="closest:[data-player-row]">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @else
                                    <span class="text-orange-600 dark:text-orange-400">{{ __('No user') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-zinc-500 dark:text-zinc-400">{{ __('No players yet. Import a CSV to begin.') }}</td>
                        </tr>
                    @endforelse
                </x-ui.data-table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
