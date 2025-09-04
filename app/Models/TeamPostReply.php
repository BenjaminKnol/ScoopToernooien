<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamPostReply extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(TeamPostThread::class, 'thread_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}
