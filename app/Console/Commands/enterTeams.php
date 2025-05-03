<?php

namespace App\Console\Commands;

use App\Models\Team;
use Illuminate\Console\Command;

class enterTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:enter-teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'add teams to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Team name');
        $this->info($name);
        $players = $this->ask('Number of players');
        $this->info($players);
        $poule = $this->ask('Poule');
        $this->info($poule);

        Team::create([
            'name' => $name,
            'number_of_players' => $players,
            'poule' => $poule,
            'points' => 0,
            'costume_rating' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
