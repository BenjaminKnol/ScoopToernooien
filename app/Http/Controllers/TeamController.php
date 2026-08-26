<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Show the authenticated user's team page.
     * Note: User-to-team linking is not implemented yet.
     */
    public function myTeam()
    {
        $player = Auth::user()->player;
        $team = $player->team;
        return view('team.my',
            [
                'player' => $player,
                'team' => $team
            ]);
    }

    /**
     * List all teams for the admin teams page.
     */
    public function index()
    {
        return view('teams.index', [
            'teams' => Team::all(),
        ]);
    }

    /**
     * Store a newly created team (admin only via middleware)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'points' => ['nullable', 'integer', 'min:0'],
            'costume_rating' => ['nullable', 'integer', 'min:0'],
        ]);

        // `points` is NOT NULL with a DB default of 0, but an explicit null in the
        // insert (from a blank form field) overrides that default rather than using it.
        $data['points'] ??= 0;

        Team::create($data);

        return redirect()->route('teams.index')->with('success', __('Team created successfully.'));
    }

    /**
     * Update the specified team
     */
    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'points' => ['nullable', 'integer', 'min:0'],
            'costume_rating' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['points'] ??= 0;

        $team->update($data);

        return redirect()->route('teams.index')->with('success', __('Team updated successfully.'));
    }

    /**
     * Remove the specified team
     */
    public function destroy(Team $team)
    {
        $team->delete();
        return redirect()->route('teams.index')->with('success', __('Team deleted successfully.'));
    }
}
