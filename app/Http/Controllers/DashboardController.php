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
        $games = Game::all();
        return view('dashboard', [
            'teams' => $teams,
            'games' => $games,
            ]);
    }
}
