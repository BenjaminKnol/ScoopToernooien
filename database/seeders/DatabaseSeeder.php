<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Benjamin Knol',
            'email' => 'benjaminknol@shcscoop.nl',
            'password' => bcrypt(Str::random(32)),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Lina van Loevezijn',
            'email' => 'linavanloevezijn@shcscoop.nl',
            'password' => bcrypt(Str::random(32)),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Bjorn van der Vuurst',
            'email' => 'bjornvandervuurst@shcscoop.nl',
            'password' => bcrypt(Str::random(32)),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Bas Olink',
            'email' => 'basolink@shcscoop.nl',
            'password' => bcrypt(Str::random(32)),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Laura van Raaij',
            'email' => 'laura@shcscoop.nl',
            'password' => bcrypt(Str::random(32)),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Indy Rube',
            'email' => 'indyrube@shcscoop.nl',
            'password' => bcrypt(Str::random(32)),
            'is_admin' => true,
        ]);

        $this->call([
            TeamSeeder::class,
//            PlayerSeeder::class,
//            GameSeeder::class,
        ]);
    }
}
