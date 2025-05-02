<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run()
    {
        Team::factory()->count(4)->create([
            'poule' => 'A'
        ]);
        Team::factory()->count(4)->create([
            'poule' => 'B'
        ]);
        Team::factory()->count(4)->create([
            'poule' => 'C'
        ]);
        Team::factory()->count(4)->create([
            'poule' => 'D'
        ]);
    }
}
