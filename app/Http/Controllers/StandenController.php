<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;

class StandenController extends Controller
{
    public function index()
    {
        $teams = Team::orderByDesc('points')->get();
        return view('welcome', ['teams' => $teams]);
    }

    public static function calculatePoints()
    {
        $games = Game::all();
        foreach (Team::all() as $team) {
            $team->update([
                'points' => 0,
            ]);
        }
        foreach ($games as $game) {
            $game->calculatePoints();
        }
    }
}
