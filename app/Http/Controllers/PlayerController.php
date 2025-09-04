<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'lastName' => ['required'],
            'email' => ['required'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'gender' => ['nullable','in:H,D'],
            'team_code' => ['nullable','regex:/^[HD][0-9]+$/'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            $username = Str::studly($data['firstName'].$data['lastName']); // FirstNameLastName
            $user = User::create([
                'name' => $username,
                'email' => $data['email'],
                'password' => $data['email'], // As requested (insecure, event-only)
                'is_admin' => false,
            ]);
        }
        $data['user_id'] = $user->id;
        $player = Player::create($data);
        return redirect()->route('dashboard')->with('success', __('Player created successfully.'));
    }

    public function show(Player $player)
    {
        return $player;
    }

    public function update(Request $request, Player $player)
    {
        $data = $request->validate([
            'firstName' => ['required'],
            'lastName' => ['required'],
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
