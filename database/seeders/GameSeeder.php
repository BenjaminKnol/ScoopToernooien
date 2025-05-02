<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        // Get all unique poules
        $poules = Team::select('poule')->distinct()->get()->pluck('poule');

        // Generate all possible time slots (13:00 to 17:00 in 15-minute intervals)
        $timeSlots = [];
        $currentTime = Carbon::create(2024, 1, 1, 13, 0, 0);
        $endTime = Carbon::create(2024, 1, 1, 17, 0, 0);

        while ($currentTime <= $endTime) {
            $timeSlots[] = $currentTime->format('H:i');
            $currentTime->addMinutes(15);
        }

        $matchRound = 0;
        $matchesPerRound = 5;

        // Create pause team
        $pauseTeam = Team::create([
            'name' => 'Pauze',
            'poule' => 'Pause'
        ]);

        // Create an array to store matches and track breaks per team
        $pouleMatches = [];
        $teamBreaks = [];
        foreach ($poules as $poule) {
            $teams = Team::where('poule', $poule)->get();
            $pouleMatches[$poule] = [];

            // Initialize break counter for each team
            foreach ($teams as $team) {
                $teamBreaks[$team->id] = 0;
            }

            // Generate all matches for this poule
            for ($i = 0; $i < count($teams); $i++) {
                for ($j = $i + 1; $j < count($teams); $j++) {
                    if ($teams[$i]->id !== $teams[$j]->id) {
                        $pouleMatches[$poule][] = [
                            'team_1_id' => $teams[$i]->id,
                            'team_2_id' => $teams[$j]->id
                        ];

                        // Add single break for each team if they haven't had one yet
                        if ($teamBreaks[$teams[$i]->id] === 0) {
                            $pouleMatches[$poule][] = [
                                'team_1_id' => $teams[$i]->id,
                                'team_2_id' => $pauseTeam->id
                            ];
                            $teamBreaks[$teams[$i]->id]++;
                        }

                        if ($teamBreaks[$teams[$j]->id] === 0) {
                            $pouleMatches[$poule][] = [
                                'team_1_id' => $teams[$j]->id,
                                'team_2_id' => $pauseTeam->id
                            ];
                            $teamBreaks[$teams[$j]->id]++;
                        }
                    }
                }
            }
        }

        // Create matches in rounds
        while (!empty(array_filter($pouleMatches))) {
            if (!isset($timeSlots[$matchRound])) {
                break; // Exit if we run out of time slots
            }

            $startTime = $timeSlots[$matchRound];
            $endTime = Carbon::createFromFormat('H:i', $startTime)
                ->addMinutes(30)
                ->format('H:i');

            // Create one match from each poule if available
            foreach ($poules as $poule) {
                if (!empty($pouleMatches[$poule])) {
                    $match = array_shift($pouleMatches[$poule]);

                    Game::create([
                        'team_1_id' => $match['team_1_id'],
                        'team_2_id' => $match['team_2_id'],
                        'startTime' => $startTime,
                        'endTime' => $endTime,
                        'outcome' => null,
                    ])->save();
                }
            }

            $matchRound++;
        }
    }
}
