<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        return Game::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'outcome' => 'string',
            'startTime' => 'required',
            'endTime' => 'required',
            'team_1_id' => 'required',
            'team_2_id' => 'required',
        ]);

        return redirect('dashboard')->with('success', 'Match added successfully.');
    }

    public function show(Game $game)
    {
        return $game;
    }

    public function update(Request $request, Game $game)
    {
        $data = $request->validate([
            'outcome' => ['required'],
        ]);

        $game->update($data);

        return redirect('dashboard')->with('success', 'Match updated successfully.');
    }



    public function destroy(Game $game)
    {
        $game->delete();

        return response()->json();
    }
}
