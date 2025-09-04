<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use App\Models\TeamPostReply;
use App\Models\TeamPostThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamPostController extends Controller
{
    public function storeThread(Request $request): RedirectResponse
    {
        $user = Auth::user();
        /** @var Player|null $player */
        $player = Player::where('user_id', $user->id)->first();
        abort_if(!$player || !$player->team_id, 403);

        $data = $request->validate([
            'title' => ['required','string','max:120'],
            'body' => ['required','string','max:5000'],
        ]);

        TeamPostThread::create([
            'team_id' => $player->team_id,
            'player_id' => $player->id,
            'title' => $data['title'],
            'body' => $data['body'],
        ]);

        return back();
    }

    public function storeReply(Request $request, TeamPostThread $thread): RedirectResponse
    {
        $user = Auth::user();
        /** @var Player|null $player */
        $player = Player::where('user_id', $user->id)->first();
        abort_if(!$player || !$player->team_id, 403);
        abort_if($thread->team_id !== $player->team_id, 403);

        $data = $request->validate([
            'body' => ['required','string','max:3000'],
        ]);

        TeamPostReply::create([
            'thread_id' => $thread->id,
            'player_id' => $player->id,
            'body' => $data['body'],
        ]);

        return back();
    }
}
