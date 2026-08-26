<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tournament_day' => 'date',
    ];

    /**
     * The tournament only ever has one settings row. Fetch it, creating it
     * (seeded from the legacy config/scheduling.php values) the first time
     * it's needed.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'number_of_fields' => config('scheduling.number_of_fields', 1),
            'available_hours' => config('scheduling.available_hours', 4),
            'tournament_day' => config('scheduling.tournament_day'),
            'start_time' => config('scheduling.start_time'),
        ]);
    }
}
