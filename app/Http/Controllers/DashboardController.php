<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $teams = Team::all();
        $pauze = Team::where('name', '=', 'Pauze')->first();
        $games = Game::whereNot('games.team_1_id', '=', $pauze->id)
            ->whereNot('games.team_2_id', '=', $pauze->id)
            ->get();
        return view('dashboard', [
            'teams' => $teams,
            'games' => $games,
            ]);
    }
}
