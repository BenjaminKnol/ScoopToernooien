<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminGameResultController extends Controller
{
    public function approve(Request $request, Game $game): RedirectResponse
    {
        // Only admins via middleware in routes, but double-check if needed
        $request->validate([
            'score' => ['required','regex:/^\d+-\d+$/'],
        ]);

        $score = $request->string('score');

        // If points already applied for a previously accepted outcome, revert first
        if ($game->points_applied) {
            $game->revertPointsIfApplied();
        }

        // Set accepted outcome and mark as accepted by admin
        $game->accepted_outcome = (string)$score;
        $game->status = 'accepted';
        $game->accepted_at = now();
        $game->verified_by_admin_id = Auth::id();
        $game->save();

        // Apply points for the new accepted outcome
        $game->applyPointsIfNeeded();

        return back()->with('success', __('Outcome approved.'));
    }
}
