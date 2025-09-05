<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        if (Carbon::now()->isBefore(Carbon::parse($game->start_time))) {
            return back()->withErrors(['score' => __('You can only report a result after the game has started.')]);
        }


        $data = $request->validate([
            'score' => ['required','regex:/^\d+-\d+$/'], // strict: no spaces, only integers around '-'
        ]);

        $normalized = $data['score']; // already strict

        // Store per-team submission
        if ($player->team_id === $game->team_1_id) {
            $game->team_1_submission = $normalized;
        } else {
            $game->team_2_submission = $normalized;
        }

        // Determine status
        if ($game->team_1_submission && $game->team_2_submission) {
            if ($game->team_1_submission === $game->team_2_submission) {
                // Agreement: accept and apply
                $game->accepted_outcome = $normalized; // both equal
                $game->status = 'accepted';
                $game->accepted_at = now();
                $game->verified_by_admin_id = null; // auto-accept by teams
                $game->save();
                $game->applyPointsIfNeeded();
                return back()->with('success', __('Result accepted.'));
            } else {
                $game->status = 'conflict';
            }
        } else {
            $game->status = 'pending';
        }

        $game->save();

        return back()->with('success', __('Result submitted. Awaiting confirmation.'));
    }
}
