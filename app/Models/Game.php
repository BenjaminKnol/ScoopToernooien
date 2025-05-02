<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function generateGames()
    {
        $teams = Team::getTeamsByPoules();
        foreach ($teams['A'] as $teams) {

        }
    }

    public function team_1()
    {
        return $this->hasOne(Team::class);
    }

    public function team_2()
    {
        return $this->hasOne(Team::class);
    }
}
