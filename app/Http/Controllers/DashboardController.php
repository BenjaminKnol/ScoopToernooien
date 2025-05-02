<?php

namespace App\Http\Controllers;

use App\Models\Team;

class DashboardController extends Controller
{
    public function index()
    {
        $teams = Team::all();
        return view('dashboard', ['teams' => $teams]);
    }
}
