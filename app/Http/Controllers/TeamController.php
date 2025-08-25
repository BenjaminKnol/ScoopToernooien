<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Show the authenticated user's team page.
     * Note: User-to-team linking is not implemented yet. This page acts as
     * a placeholder and can be expanded once accounts are linked to teams/players.
     */
    public function myTeam()
    {
        return view('team.my');
    }
}
