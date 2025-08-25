<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index()
    {
        return Player::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'firstName' => ['required'],
            'secondName' => ['required'],
            'team_id' => ['required', 'exists:teams'],
        ]);

        return Player::create($data);
    }

    public function show(Player $player)
    {
        return $player;
    }

    public function update(Request $request, Player $player)
    {
        $data = $request->validate([
            'firstName' => ['required'],
            'secondName' => ['required'],
            'team_id' => ['required', 'exists:teams'],
        ]);

        $player->update($data);

        return $player;
    }

    public function destroy(Player $player)
    {
        $player->delete();

        return response()->json();
    }
}
