<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function applyPointsIfNeeded(): void
    {
        if ($this->status !== 'accepted' || !$this->accepted_outcome) {
            return;
        }
        if ($this->points_applied) {
            return; // already applied
        }
        [$a, $b] = array_map('intval', explode('-', $this->accepted_outcome));
        $team1 = Team::findOrFail($this->team_1_id);
        $team2 = Team::findOrFail($this->team_2_id);

        if ($a > $b) {
            $team1->increment('points', 3);
        } elseif ($a < $b) {
            $team2->increment('points', 3);
        } else {
            $team1->increment('points', 1);
            $team2->increment('points', 1);
        }

        $this->forceFill(['points_applied' => true])->save();
    }

    public function revertPointsIfApplied(): void
    {
        if (!$this->points_applied || !$this->accepted_outcome) {
            return;
        }
        [$a, $b] = array_map('intval', explode('-', $this->accepted_outcome));
        $team1 = Team::findOrFail($this->team_1_id);
        $team2 = Team::findOrFail($this->team_2_id);

        if ($a > $b) {
            $team1->decrement('points', 3);
        } elseif ($a < $b) {
            $team2->decrement('points', 3);
        } else {
            $team1->decrement('points', 1);
            $team2->decrement('points', 1);
        }

        $this->forceFill(['points_applied' => false])->save();
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

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team_1_id');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team_2_id');
    }
}
