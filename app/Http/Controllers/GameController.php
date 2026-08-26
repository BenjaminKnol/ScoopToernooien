<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        return view('games.index', [
            'teams' => Team::all(),
            'games' => Game::all(),
            'conflicts' => Game::where('status', 'conflict')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'team_1_id' => ['required', 'integer'],
            'team_2_id' => ['required', 'integer', 'different:team_1_id'],
            'field' => ['required', 'integer'],
        ]);

        $data['start_time'] = (string)Carbon::today()->setTimeFromTimeString($request->start_time);
        $data['end_time'] = (string)Carbon::today()->setTimeFromTimeString($request->end_time);

        Game::create($data);

        return redirect()->route('games.index')->with('success', 'Match added successfully.');
    }

    public function show(Game $game)
    {
        return $game;
    }

    public function update(Request $request, Game $game)
    {
        $data = $request->validate([
            'start_time' => ['sometimes', 'string'],
            'end_time' => ['sometimes', 'string'],
            'team_1_id' => ['sometimes'],
            'team_2_id' => ['sometimes'],
            'field' => ['sometimes', 'numeric'],
        ]);

        // start_time/end_time are stored as full datetimes, but this form only edits
        // the time-of-day, so keep the game's existing date and swap in the new time.
        if (isset($data['start_time'])) {
            $data['start_time'] = (string) Carbon::parse($game->start_time)->setTimeFromTimeString($data['start_time']);
        }
        if (isset($data['end_time'])) {
            $data['end_time'] = (string) Carbon::parse($game->end_time)->setTimeFromTimeString($data['end_time']);
        }

        $game->update($data);

        return redirect()->route('games.index')->with('success', 'Match updated successfully.');
    }

    public function destroy(Game $game)
    {
        $game->delete();
        return redirect()->route('games.index')->with('success', 'Match deleted successfully.');
    }
}
