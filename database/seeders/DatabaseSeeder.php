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
        ]);

        User::factory()->create([
            'name' => 'Youp Evers',
            'email' => 'youpevers@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorYoup')
        ]);

        User::factory()->create([
            'name' => 'Tygo Hilting',
            'email' => 'tygohilting@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorTygo')
        ]);

        User::factory()->create([
            'name' => 'Ingmar Huizing',
            'email' => 'ingmarhuizing@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorIngmar')
        ]);

        User::factory()->create([
            'name' => 'Rithik Putatunda',
            'email' => 'rithikputatunda@shcscoop.nl',
            'password' => bcrypt('EenOrigineelWachtwoordVoorRithik')
        ]);
        $this->call([
            TeamSeeder::class,
        ]);
    }
}
