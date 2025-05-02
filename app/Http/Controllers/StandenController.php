<?php

namespace App\Http\Controllers;

use App\Models\Team;

class StandenController extends Controller
{
    public function index()
        {
            return view('welcome', Team::getTeamsByPoules());
        }
}
