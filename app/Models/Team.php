<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class Team extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function upcomingGames() : Collection
    {
        return Game::where(function($q) {
                $q->where('team_1_id', $this->id)
                  ->orWhere('team_2_id', $this->id);
            })
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->get();
    }

    public function games1() : Relation
    {
        return $this->hasMany(Game::class, 'team_1_id', 'id');
    }

    public function games2() : Relation
    {
        return $this->hasMany(Game::class, 'team_2_id', 'id');
    }

    public function players() : Relation
    {
        return $this->hasMany(Player::class);
    }

    public function postThreads(): Relation
    {
        return $this->hasMany(\App\Models\TeamPostThread::class);
    }
}
