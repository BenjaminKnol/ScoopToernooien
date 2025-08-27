<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function create()
    {
        $defaults = [
            'match_length_minutes' => 20,
            'max_consecutive_matches' => 2,
            'max_idle_breaks' => 1,
        ];
        $fields = (int) (env('NUMBER_OF_FIELDS', 1));
        $hours = (float) (env('AVAILABLE_HOURS', 4));

        return view('dashboard.generateSchedule', [
            'defaults' => $defaults,
            'numberOfFields' => $fields,
            'availableHours' => $hours,
            'result' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'match_length_minutes' => ['required','integer','min:5','max:90'],
            'max_consecutive_matches' => ['required','integer','min:1','max:10'],
            'max_idle_breaks' => ['required','integer','min:0','max:10'],
        ]);

        $fields = (int) (env('NUMBER_OF_FIELDS', 1));
        $hours = (float) (env('AVAILABLE_HOURS', 4));

        // Compute the number of time slots available based on fields and hours
        $totalMinutes = (int) round($hours * 60);
        $slotLength = (int) $data['match_length_minutes'];
        $slotsPerField = intdiv($totalMinutes, $slotLength);
        $totalSlots = $slotsPerField * max(1, $fields);

        // For this initial implementation, we return a simple structure
        $result = [
            'summary' => [
                'number_of_fields' => $fields,
                'available_hours' => $hours,
                'match_length_minutes' => $slotLength,
                'slots_per_field' => $slotsPerField,
                'total_slots' => $totalSlots,
                'constraints' => [
                    'max_consecutive_matches' => (int)$data['max_consecutive_matches'],
                    'max_idle_breaks' => (int)$data['max_idle_breaks'],
                ],
            ],
            'schedule' => [],
        ];

        return view('dashboard.generateSchedule', [
            'defaults' => $data,
            'numberOfFields' => $fields,
            'availableHours' => $hours,
            'result' => $result,
        ]);
    }
}
