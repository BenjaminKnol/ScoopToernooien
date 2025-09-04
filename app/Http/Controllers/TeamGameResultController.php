<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamGameResultController extends Controller
{
    public function report(Request $request, Game $game): RedirectResponse
    {
        $user = Auth::user();
        /** @var Player|null $player */
        $player = $user?->player;
        abort_if(!$player || !$player->team_id, 403);

        // Ensure this player is part of the game
        abort_if(!in_array($player->team_id, [$game->team_1_id, $game->team_2_id], true), 403);

        // Only allow reporting after the game start time (or end time if available)
        // Keep minimal: allow if start_time <= now()
        if (now()->lt(optional(\Illuminate\Support\Carbon::parse($game->start_time)))) {
            return back()->withErrors(['score' => __('You can only report a result after the game has started.')]);
        }

        $data = $request->validate([
            'score' => ['required','regex:/^\d+\s*-\s*\d+$/'],
        ]);

        // Normalize score to "a-b"
        $normalized = preg_replace('/\s*/', '', $data['score']);

        // Minimal approach: immediately accept and apply points once
        $game->outcome = $normalized;
        $game->accepted_outcome = $normalized;
        $game->status = 'accepted';
        $game->save();
        // Apply points if not yet applied (Game has a guard method)
        $game->applyPointsIfNeeded();

        return back()->with('success', __('Result reported.'));
    }
}
