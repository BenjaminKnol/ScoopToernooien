<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Timeslot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TimeslotFactory extends Factory
{
    protected $model = Timeslot::class;

    public function definition(): array
    {
        $nFields = $this->faker->numberBetween(1, 10);
        $teams = Team::getTeamsByPoules();
        $poule = $this->faker->randomElement(array_keys($teams));
        $team1 = $this->faker->randomElement($teams[$poule]);
        $team2 = $this->faker->randomElement($teams[$poule]);
        // TODO: find a more clever solution. (pop from array?) also currrently only 1 game. should be as many as fields...
        while ($team1 == $team2) {
            $team2 = $this->faker->randomElement($teams[$poule]);
        }
        $games = [
            'team_1' => $team1,
            'team_2' => $team2,
        ];
        return [
            'fields' => $nFields,
            'games' => $games,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
