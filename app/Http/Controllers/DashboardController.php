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
        $conflicts = Game::where('status', 'conflict')->get();

        return view('dashboard', [
            'teams' => $teams,
            'games' => $games,
            'players' => $players,
            'conflicts' => $conflicts,
        ]);
    }
}
