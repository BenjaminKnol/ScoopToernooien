<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GameController extends Controller
{
    public function index()
    {
        return Game::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'outcome' => 'nullable|string',
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'team_1_id' => ['required', 'integer'],
            'team_2_id' => ['required', 'integer', 'different:team_1_id'],
            'field' => ['required', 'integer'],
        ]);

        $game = Game::create($data);

        return redirect('dashboard')->with('success', 'Match added successfully.');
    }

    public function show(Game $game)
    {
        return $game;
    }

    public function update(Request $request, Game $game)
    {
        $data = $request->validate([
            'outcome' => ['nullable', 'string'],
            'start_time' => ['sometimes', 'string'],
            'end_time' => ['sometimes', 'string'],
            'team_1_id' => ['sometimes'],
            'team_2_id' => ['sometimes'],
            'field' => ['sometimes', 'numeric'],
        ]);

        $game->update($data);

        // Recalculate points only when outcome is provided (or changed)
        if ($request->filled('outcome')) {
            StandenController::calculatePoints();
        }

        return redirect('dashboard')->with('success', 'Match updated successfully.');
    }

    public function destroy(Game $game)
    {
        $game->delete();
        return redirect('dashboard')->with('success', 'Match deleted successfully.');
    }
}
