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
        $players = Player::with(['team','user'])->orderBy('team_id')->orderBy('secondName')->get();
        return view('dashboard', [
            'teams' => $teams,
            'games' => $games,
            'players' => $players,
        ]);
    }
}
