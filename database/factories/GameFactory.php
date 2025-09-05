<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game> */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addMinutes(10),
            'team_1_id' => $this->faker->randomNumber(),
            'team_2_id' => $this->faker->randomNumber(),
            'field' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
