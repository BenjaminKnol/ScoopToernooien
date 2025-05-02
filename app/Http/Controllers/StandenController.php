<?php

namespace App\Http\Controllers;

use App\Models\Team;

class StandenController extends Controller
{
    private const WINNERS_POULE = 'Winnaars';
    private const LOSERS_POULE = 'Verliezers';

    public function index()
    {
        return view('welcome', Team::getTeamsByPoules());
    }

    public function splitPoulesIntoWinnersAndLosers()
    {
        $poules = Team::select('poule')
            ->whereNotIn('poule', [self::WINNERS_POULE, self::LOSERS_POULE, 'Pause'])
            ->distinct()
            ->get()
            ->pluck('poule');

        foreach ($poules as $poule) {
            $teams = Team::where('poule', $poule)
                ->orderBy('points', 'desc')
                ->get();

            $middleIndex = ceil($teams->count() / 2);

            $this->updateTeamPoules($teams->take($middleIndex), self::WINNERS_POULE);
            $this->updateTeamPoules($teams->skip($middleIndex), self::LOSERS_POULE);
        }

        return redirect()->back()->with('success', 'Poules gesplitst, niet nog een keer drukken por favor');
    }

    private function updateTeamPoules($teams, string $newPoule): void
    {
        foreach ($teams as $team) {
            $team->poule = $newPoule;
            $team->save();
        }
    }
}
