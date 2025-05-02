<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Team extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function getTeamsByPoules(): array
    {
        $allTeams = Team::all();
        return [
            'A' => $allTeams->where('poule', '=', 'A'),
            'B' => $allTeams->where('poule', '=', 'B'),
            'C' => $allTeams->where('poule', '=', 'C'),
            'D' => $allTeams->where('poule', '=', 'D')
        ];
    }

    public function games1()
    {
        return $this->hasMany(Game::class, 'team_1_id', 'id');
    }

    public function games2()
    {
        return $this->hasMany(Game::class, 'team_2_id', 'id');
    }
}
