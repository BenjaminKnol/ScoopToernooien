<x-layouts.app.header>
    @if (session('success'))
        <x-alert type="success">
            {{ session('success') }}
        </x-alert>
    @endif
    <div class="flex h-full w-full flex-1 flex-col gap-8 rounded-xl mt-2">
        <div class="relative overflow-hidden rounded-xl mb-4 border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Games</h2>
                <div class="space-x-3">
                    <a href="{{ route('dashboard.generateSchedule') }}"
                       class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Generate schedule') }}</a>
                </div>
            </div>
            <form method="POST" action="{{ route('games.store') }}" class="space-y-4 mb-6">
                @csrf
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
            <h3 class="mb-4 text-md font-semibold">Manage Games</h3>
            <div class="space-y-6">
                @foreach($games as $game)
                        <div class="space-y-3 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <div class="flex items-center justify-between text-sm">
                                <div>{{ optional($game->team_1()->first())->name ?? ('Team #'.$game->team_1_id) }} vs {{ optional($game->team_2()->first())->name ?? ('Team #'.$game->team_2_id) }}</div>
                                <div class="text-gray-500">{{ $game->start_time }} - {{ $game->end_time }} · Field {{ $game->field + 1 }}</div>
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
        <!-- Teams: Create & Manage combined -->
        <div class="relative overflow-hidden rounded-xl border mb-4 border-neutral-200 dark:border-neutral-700 p-4">
            <h2 class="mb-4 text-lg font-semibold">Teams</h2>
            <form method="POST" action="{{ route('teams.store') }}" class="space-y-3 mb-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                </div>
                <div class="grid grid-cols-3 gap-3">
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

            <h3 class="mb-4 text-md font-semibold">Manage Teams</h3>
            <div class="space-y-4">
                @foreach($teams as $team)
                    <form method="POST" action="{{ route('teams.update', $team->id) }}" class="grid grid-cols-5 gap-3 items-end border-b border-gray-200 pb-3 dark:border-gray-700">
                        @csrf
                        @method('PUT')
                        <div class="col-span-2">
                            <label class="block text-sm">Name</label>
                                                        @if(!empty($team->color_hex) || !empty($team->color_name))
                                                            <div class="mt-1 text-xs text-zinc-700 dark:text-zinc-300 inline-flex items-center gap-2">
                                                                <span class="inline-block h-3 w-3 rounded-full border border-zinc-300 dark:border-zinc-600" style="background-color: {{ $team->color_hex ?? '#ccc' }}"></span>
                                                                <span>{{ $team->color_name ?? '' }} @if(!empty($team->color_hex)) ({{ $team->color_hex }}) @endif</span>
                                                            </div>
                                                        @endif
                            <input type="text" name="name" value="{{ $team->name }}" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
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
                    <div class="mt-2 mb-4">
                        <div class="text-sm font-medium mb-1">{{ __('Players in this team') }}</div>
                        @php $teamPlayers = $team->players; @endphp
                        @if($teamPlayers->isEmpty())
                            <div class="text-xs text-gray-500">{{ __('No players in this team.') }}</div>
                        @else
                            <ul class="text-sm space-y-1">
                                @foreach($teamPlayers as $p)
                                    <li class="flex items-center justify-between">
                                        <span>{{ $p->firstName }} {{ $p->lastName }} <span class="text-xs text-gray-500">({{ $p->email }})</span></span>
                                        <form method="POST" action="{{ route('players.update', $p->id) }}" onsubmit="return confirm('{{ __('Remove this player from the team?') }}');">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="team_id" value="">
                                            <button type="submit" class="inline-flex justify-center rounded-md bg-amber-600 px-2 py-1 text-xs font-medium text-white hover:bg-amber-700">{{ __('Remove') }}</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Players: Import & Assign -->
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Players</h2>
                <a href="{{ route('dashboard.autoAssignTeams') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Auto-assign teams (preview)') }}</a>
            </div>
            <div class="grid gap-6">
                <!-- Import CSV -->
                <form method="POST" action="{{ route('players.import') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Import CSV</label>
                    <input type="file" name="csv" accept=".csv,text/csv" class="mt-1 block w-full rounded-md border-2 border-gray-300" required />
                    <p class="text-xs text-gray-500">Required columns: Voornaam, Achternaam, Email. Optional: Team (e.g., H1 or D3) — gender will be inferred from Team.</p>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Upload</button>
                    </div>
                </form>
                <!-- Manually create a player -->
                <p class="block text-sm font-medium text-gray-700 dark:text-gray-200">Manual player creation</p>
                <form method="POST" id="createPlayerManually" action="{{ route('players.store') }}" class="grid grid-cols-6 gap-3 items-end border-gray-200 pb-3 dark:border-gray-700">
                    @csrf
                    <div class="">
                        <label class="block text-sm">{{ __('firstName') }}</label>
                        <input type="text" name="firstName" value="{{ old('firstName') }}" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                    <div class="">
                        <label class="block text-sm">{{ __('lastName') }}</label>
                        <input type="text" name="lastName" value="{{ old('lastName') }}" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                    <div class="">
                        <label class="block text-sm">E-mail</label>
                        <input type="text" name="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                    <div class="h-full">
                        <label class="block text-sm">Team</label>
                        <select name="team_id" class="mt-1 block w-full rounded-md border-2 border-gray-300">
                            <option value="">— Unassigned —</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="h-full">
                        <label class="block text-sm">Gender</label>
                        <select name="gender" class="mt-1 block w-full rounded-md border-2 border-gray-300">
                            <option value="">— Unknown —</option>
                            <option value="H">H (Male)</option>
                            <option value="D">D (Female)</option>
                        </select>
                    </div>
                    <div class="h-full">
                        <label class="block text-sm">Team code</label>
                        <input type="text" name="team_code" value="{{ old('team_code') }}" placeholder="e.g. H1 or D3" class="mt-1 block w-full rounded-md border-2 border-gray-300" />
                    </div>
                    <div class="flex justify-end col-span-6">
                        <button type="submit" form="createPlayerManually" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Create Player</button>
                    </div>
                </form>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 pr-4">Name</th>
                                    <th class="py-2 pr-4">Email</th>
                                    <th class="py-2 pr-4">Gender</th>
                                    <th class="py-2 pr-4">Team code</th>
                                    <th class="py-2 pr-4">Team</th>
                                    <th class="py-2 pr-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($players as $player)
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="py-2 pr-4">{{ $player->firstName }} {{ $player->lastName }}</td>
                                        <td class="py-2 pr-4">{{ $player->email }}</td>
                                        <td class="py-2 pr-4">{{ $player->gender ?? '—' }}</td>
                                        <td class="py-2 pr-4">{{ $player->team_code ?? '—' }}</td>
                                        <td class="py-2 pr-4">
                                            <form method="POST" action="{{ route('players.update', $player->id) }}" class="flex items-center gap-2 justify-end md:justify-start">
                                                @csrf
                                                @method('PUT')
                                                <select name="team_id" class="mt-1 block rounded-md border-2 border-gray-300 autosave-select">
                                                    <option value="">— Unassigned —</option>
                                                    @foreach($teams as $team)
                                                        <option value="{{ $team->id }}" @selected($player->team_id === $team->id)>{{ $team->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="hidden md:inline-flex justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">Save</button>
                                                <button type="submit" form="delete-user-{{ $player->id }}" class="inline-flex justify-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700" onclick="return confirm('Delete this player?');">Delete</button>
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
<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  function showToast(msg, type='success'){
    const div = document.createElement('div');
    div.className = 'fixed top-2 inset-x-0 z-50 mx-auto max-w-3xl rounded-lg p-3 shadow-lg text-sm ' + (type==='error' ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800');
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(()=>{ div.remove(); }, 3000);
  }
  async function ajaxSubmit(form){
    const url = form.action;
    let method = (form.querySelector('input[name="_method"]')?.value || form.method || 'POST').toUpperCase();
    const formData = new FormData(form);
    // If method spoofing present, send as POST
    const fetchMethod = method === 'GET' ? 'GET' : 'POST';
    const res = await fetch(url, {
      method: fetchMethod,
      headers: { 'X-CSRF-TOKEN': csrf || '', 'X-Requested-With': 'XMLHttpRequest' },
      body: fetchMethod==='GET' ? null : formData,
      credentials: 'same-origin'
    });
    // Try to parse JSON; if not, accept HTML redirects but don’t navigate
    const text = await res.text();
    // Heuristic: find success message in response
    let ok = res.ok;
    if(ok){
      showToast('Saved successfully');
    } else {
      showToast('Failed to save', 'error');
    }
    return ok;
  }
  // Attach autosave to team selects (players table)
  document.querySelectorAll('form[action*="/players/"] select.autosave-select').forEach(sel => {
    sel.addEventListener('change', async (e) => {
      e.preventDefault();
      const form = sel.closest('form');
      try { await ajaxSubmit(form); } catch(err){ showToast('Error', 'error'); }
    });
  });
  // Make selected dashboard management forms AJAX to avoid page reload
  // Mark forms with data-ajax="true" in markup? We can target known sections:
  const selectors = [
    'form[action*="/games/"]',
    'form[action*="/teams/"]',
    'form[action*="/players/"]'
  ];
  document.querySelectorAll(selectors.join(',')).forEach(form => {
    // Skip the create forms (they have no id-specific action typically ending with /games or /teams exactly)
    const isCreate = /\/games$|\/teams$|\/players$/.test(form.action) && !form.querySelector('input[name="_method"][value="PUT"], input[name="_method"][value="DELETE"]');
    if(isCreate) return;
    // For delete buttons that reference separate hidden forms, we leave them; hidden forms will also be caught.
    form.addEventListener('submit', async (e) => {
      // Only handle if user didn’t request download/navigation
      e.preventDefault();
      const submitter = e.submitter;
      if(submitter && submitter.hasAttribute('form')){
        // Let linked hidden form handle separately
      }
      try {
        const ok = await ajaxSubmit(form);
        if(ok){
          // If this is a delete form, remove the wrapper row/card
          const isDelete = (form.querySelector('input[name="_method"][value="DELETE"]')!=null);
          if(isDelete){
            // remove closest border-b block or table row
            const row = form.closest('tr, .border-b, .space-y-3');
            if(row) row.remove();
          }
        }
      } catch(err){ showToast('Error', 'error'); }
    }, { passive: false });
  });
})();
</script>
</x-layouts.app.header>
