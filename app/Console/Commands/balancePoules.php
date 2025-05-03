<?php

namespace App\Console\Commands;

use App\Models\Team;
use Illuminate\Console\Command;

class balancePoules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poule:balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Balance teams across poules so they differ by at most one member';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teams = Team::all();

        $totalTeams = count($teams);

        if ($totalTeams === 0) {
            $this->error('No teams found');
            return 1;
        }

        $poules = ['A', 'B', 'C', 'D'];
        $poulesCount = 4;

        $baseSize = intdiv($totalTeams, $poulesCount);
        $remainder = $totalTeams % $poulesCount;

        $currentIndex = 0;

        foreach ($poules as $pouleIndex => $pouleName) {
            // Calculate how many teams this poule should have
            $teamsInThisPoule = $baseSize + ($pouleIndex < $remainder ? 1 : 0);

            // Assign teams to this poule
            for ($i = 0; $i < $teamsInThisPoule; $i++) {
                if ($currentIndex < $teams->count()) {
                    $team = $teams[$currentIndex];
                    $team->poule = $pouleName;
                    $team->save();
                    $currentIndex++;
                }
            }
        }
        $this->info('Poules have been balanced successfully!');

        // Show the distribution
        foreach ($poules as $poule) {
            $count = Team::where('poule', $poule)->count();
            $this->line("Poule {$poule}: {$count} teams");
        }

        return 0;
    }
}
