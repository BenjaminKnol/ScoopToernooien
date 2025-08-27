<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Team::all() as $team) {
            $users = User::factory()->count(random_int(5,7))->create();
            foreach ($users as $user){
                Player::create([
                    'firstName' => explode(' ', $user->name)[0],
                    'lastName' => explode(' ', $user->name)[1],
                    'user_id' => $user->id,
                    'team_id' => $team->id,
                    'email' => $user->email,
                ]);
            }
        }
    }
}
