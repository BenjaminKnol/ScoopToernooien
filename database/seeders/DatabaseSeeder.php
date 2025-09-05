<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            'password' => bcrypt('HuizentoernooiZonderWijWonenIsNietHetzelfde'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Youp Evers',
            'email' => 'youpevers@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorYoup'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Tygo Hillen',
            'email' => 'tygohilting@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorTygo'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Ingmar Huizing',
            'email' => 'ingmarhuizing@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorIngmar'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Rithik Putatunda',
            'email' => 'rithikputatunda@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorRithik'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Puck de nooy',
            'email' => 'puckdenooy@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorPuck'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Klaas Jan Gerritsen',
            'email' => 'klaasjan@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorKlaas'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Alexandra Krinkels',
            'email' => 'alexandrakrinkels@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorAlexandra'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Lina van Loevezijn',
            'email' => 'linavanloevezijn@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorLina'),
            'is_admin' => true,
        ]);

        $this->call([
            TeamSeeder::class,
//            PlayerSeeder::class,
//            GameSeeder::class,
        ]);
    }
}
