<?php

namespace App\Http\Controllers;

use App\Models\TournamentSetting;
use Illuminate\Http\Request;

class TournamentSettingController extends Controller
{
    public function edit()
    {
        return view('settings.tournament', [
            'settings' => TournamentSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'number_of_fields' => ['required', 'integer', 'min:1', 'max:50'],
            'available_hours' => ['required', 'numeric', 'min:0.5', 'max:48'],
            'tournament_day' => ['required', 'date'],
            'start_time' => ['required', 'string'],
        ]);

        TournamentSetting::current()->update($data);

        return redirect()->route('tournament-settings.edit')->with('success', __('Tournament settings updated successfully.'));
    }
}
