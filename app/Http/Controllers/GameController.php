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

    public function show(Game $match)
    {
        return $match;
    }

    public function update(Request $request, Game $match)
    {
        $data = $request->validate([
            'outcome' => ['required'],
            'startTime' => ['required', 'date'],
            'endTime' => ['required', 'date'],
            'opponents' => ['required', 'exists:teams'],
        ]);

        $match->update($data);

        return $match;
    }

    public function destroy(Game $match)
    {
        $match->delete();

        return response()->json();
    }
}
