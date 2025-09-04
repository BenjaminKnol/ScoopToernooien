<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use App\Models\Player;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $teams = Team::all();
        $games = Game::all();
        $players = Player::with(['team','user'])->orderByRaw('team_id IS NOT NULL')->get();

        // Defaults for embedded Auto-assign form
        $teamsCount = $teams->count();
        $playersCount = $players->count();
        $defaultTarget = $teamsCount > 0 ? (int)ceil($playersCount / max(1, $teamsCount)) : 0;
        $autoAssignDefaults = [
            'target_team_size' => $defaultTarget,
            'gender_weight' => 0.3,
            'code_weight' => 1.0,
            'max_team_size_variance' => 1,
            'reassign_existing' => false,
        ];

        return view('dashboard', [
            'teams' => $teams,
            'games' => $games,
            'players' => $players,
            'autoAssignDefaults' => $autoAssignDefaults,
        ]);
    }
}
