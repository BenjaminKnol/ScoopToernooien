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

    public function calculatePoints() : void
    {
        $team_1 = Team::where('id', '=', $this->team_1_id)->first();
        $team_2 = Team::where('id', '=', $this->team_2_id)->first();
        if(isset($this->outcome)){
            $outcomes = explode('-', $this->outcome);
            if($outcomes[0] > $outcomes[1]){
                $team_1->update(['points' => $team_1->points + 3]);
            } elseif ($outcomes[0] < $outcomes[1]) {
                $team_2->update(['points' => $team_2->points + 3]);
            } elseif ($outcomes[0] === $outcomes[1]) {
                $team_1->update(['points' => $team_1->points + 1]);
                $team_2->update(['points' => $team_2->points + 1]);
            }
        }
    }

    public function opponent(int $id)
    {
        if($id === $this->team_1_id){
            return Team::find($this->team_2_id);
        }
        if($id === $this->team_2_id){
            return Team::find($this->team_1_id);
        }
    }

    public function team_1()
    {
        return $this->hasOne(Team::class, 'id', 'team_1_id');
    }

    public function team_2()
    {
        return $this->hasOne(Team::class, 'id', 'team_2_id');
    }
}
