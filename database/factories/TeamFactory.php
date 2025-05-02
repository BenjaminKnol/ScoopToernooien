<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition()
    {
        return [
            'name' => fake()->company(),
            'points' => fake()->numberBetween(0, 100),
            'costume_rating' => fake()->numberBetween(1, 10),
            'number_of_players' => fake()->numberBetween(5, 10),
            'poule' => fake()->randomElement(['A', 'B', 'C', 'D']),
        ];
    }
}
