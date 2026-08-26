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
        return view('dashboard', [
            'teamsCount' => Team::count(),
            'gamesCount' => Game::count(),
            'playersCount' => Player::count(),
            'conflictsCount' => Game::where('status', 'conflict')->count(),
        ]);
    }
}
