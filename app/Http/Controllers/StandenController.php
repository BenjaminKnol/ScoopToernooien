<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Request;

class StandenController extends Controller
{
    private const WINNERS_POULE = 'Winnaars';
    private const LOSERS_POULE = 'Verliezers';

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
            ]);;
        }
        foreach ($games as $game) {
            $game->calculatePoints();
        }
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

        Game::destroy(Game::all());

        $this->scheduleGroupPhase();

        return redirect()->back()->with('success', 'Poules gesplitst, niet nog een keer drukken por favor');
    }

    public function scheduleGroupPhase()
    {
        Game::destroy(Game::all());

        $teams = Team::whereNotNull('poule')->get();

        if ($teams->isEmpty()) {
            return response()->json(['error' => 'No teams with a poule assigned'], 422);
        }

        $grouped = $teams->groupBy('poule')->sortKeys(); // group by poule A, B, C...

        if (count($grouped) > 2) {
            $startingTime = 13;
        } else {
            $startingTime = 15;
        }

        $allMatches = collect();
        foreach ($grouped as $group => $teamsInGroup) {
            $matches = $this->roundRobin($teamsInGroup->values());
            $allMatches = $allMatches->merge($matches);
        }

        $slots = $this->scheduleMatches($allMatches->toArray());

        $startTime = Carbon::createFromTime($startingTime, 0);
        $schedule = [];

        foreach ($slots as $i => $slot) {
            $schedule[] = [
                'time' => $startTime->copy()->addMinutes($i * 15)->format('H:i'),
                'games' => collect($slot)->map(fn($match) => [
                    'team_a' => $match[0]->id,
                    'team_b' => $match[1]->id,
                ])->values(),
            ];
        }

        foreach ($schedule as $i => $slot) {
            for ($j = 0; $j < $slot['games']->count(); $j++) {
                Game::create([
                    'team_1_id' => $slot['games'][$j]['team_a'],
                    'team_2_id' => $slot['games'][$j]['team_b'],
                    'startTime' => $slot['time'],
                    'endTime' => Carbon::parse($slot['time'])->addMinutes(15)->format('H:i'),
                    'outcome' => null,
                    'field' => $j
                ]);
            }
        }

        return redirect('dashboard')->with('success', 'Group phase scheduled successfully.');
    }

    private function roundRobin(Collection $teams): array
    {
        $matches = [];
        $count = $teams->count();

        for ($i = 0; $i < $count - 1; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $matches[] = [$teams[$i], $teams[$j]];
            }
        }

        return $matches;
    }

    private function scheduleMatches(array $matches): array
    {
        $slots = [];
        $teamLastPlayedAt = [];

        foreach ($matches as $match) {
            [$teamA, $teamB] = $match;
            $placed = false;

            for ($slotIndex = 0; $slotIndex <= count($slots); $slotIndex++) {
                if (!isset($slots[$slotIndex])) {
                    $slots[$slotIndex] = [];
                }

                // Skip if slot is full (max 5 games = 10 teams)
                if (count($slots[$slotIndex]) >= 5) {
                    continue;
                }

                $aPlayed = $teamLastPlayedAt[$teamA->id] ?? -10;
                $bPlayed = $teamLastPlayedAt[$teamB->id] ?? -10;

                // Avoid 3+ consecutive games
                if (
                    ($slotIndex - $aPlayed <= 3 && $slotIndex - ($teamLastPlayedAt[$teamA->id - 1] ?? -10) == 3) ||
                    ($slotIndex - $bPlayed <= 3 && $slotIndex - ($teamLastPlayedAt[$teamB->id - 1] ?? -10) == 3)
                ) {
                    continue;
                }

                // Check for conflicts with others in the slot
                $teamsInSlot = array_merge(...array_map(fn($m) => [$m[0]->id, $m[1]->id], $slots[$slotIndex]));
                if (in_array($teamA->id, $teamsInSlot) || in_array($teamB->id, $teamsInSlot)) {
                    continue;
                }

                // Place match
                $slots[$slotIndex][] = $match;
                $teamLastPlayedAt[$teamA->id] = $slotIndex;
                $teamLastPlayedAt[$teamB->id] = $slotIndex;
                $placed = true;
                break;
            }

            if (!$placed) {
                // Last resort: new slot at the end
                $slots[] = [$match];
                $slotIndex = count($slots) - 1;
                $teamLastPlayedAt[$teamA->id] = $slotIndex;
                $teamLastPlayedAt[$teamB->id] = $slotIndex;
            }
        }

        return $slots;
    }


    private function updateTeamPoules($teams, string $newPoule): void
    {
        foreach ($teams as $team) {
            $team->poule = $newPoule;
            $team->save();
        }
    }
}
