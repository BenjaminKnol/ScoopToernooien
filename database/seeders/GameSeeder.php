<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Team;
use Dflydev\DotAccessData\Data;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class GameSeeder extends Seeder
{
    private const MAX_CONCURRENT_GAMES = 5;

    public function run(): void
    {

    }
}
