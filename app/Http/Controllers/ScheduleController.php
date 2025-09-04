<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Carbon\Carbon;
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
        $fields = (int) config('scheduling.number_of_fields', 1);
        $hours = (float) config('scheduling.available_hours', 4);

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

        $fields = (int) config('scheduling.number_of_fields', 1);
        $hours  = (float) config('scheduling.available_hours', 4);
        $day    = (string) config('scheduling.tournament_day');
        $start  = (string) config('scheduling.start_time');

        $match = (int) $data['match_length_minutes'];
        $buffer = 5; // fixed buffer between matches
        $slotLength = $match + $buffer;

        $startAt = $day && $start ? Carbon::parse($day.' '.$start) : Carbon::now();
        $totalMinutes = (int) round($hours * 60);
        $endAt = (clone $startAt)->addMinutes($totalMinutes);

        // Build timeslots per field including 5-minute buffer between matches
        $slotsPerField = [];
        for ($f = 1; $f <= max(1, $fields); $f++) {
            $t = $startAt->copy();
            $slotsPerField[$f] = [];
            while ($t->lt($endAt)) {
                $matchStart = $t->copy();
                $matchEnd = $t->copy()->addMinutes($match);
                if ($matchEnd->gt($endAt)) break; // don’t exceed available time
                $slotsPerField[$f][] = [
                    'field' => $f,
                    'start' => $matchStart->toDateTimeString(),
                    'end'   => $matchEnd->toDateTimeString(),
                ];
                $t->addMinutes($slotLength); // advance by match + buffer
            }
        }

        $slotsPerFieldCount = array_map(fn($s) => count($s), $slotsPerField);
        $totalSlots = array_sum($slotsPerFieldCount);

        // Build simple round-robin pairings and assign to slots
        $teams = Team::orderBy('name')->get(['id','name']);
        $teamIds = $teams->pluck('id')->all();
        $teamNames = $teams->pluck('name','id');

        $pairings = [];
        if (count($teamIds) > 0) {
            $pairings = $this->roundRobinPairs($teamIds);
        }
        $enriched = [];
        $pairIndex = 0;
        foreach ($slotsPerField as $field => $slots) {
            foreach ($slots as $slot) {
                if (!isset($pairings[$pairIndex])) {
                    // No more pairings; leave slot empty
                    $enriched[$field][] = $slot + [
                        'team_1_id' => null,
                        'team_2_id' => null,
                        'team_1_name' => null,
                        'team_2_name' => null,
                        'start_hm' => Carbon::parse($slot['start'])->format('H:i'),
                        'end_hm' => Carbon::parse($slot['end'])->format('H:i'),
                    ];
                    continue;
                }
                [$t1, $t2] = $pairings[$pairIndex++];
                $enriched[$field][] = $slot + [
                    'team_1_id' => $t1,
                    'team_2_id' => $t2,
                    'team_1_name' => $t1 ? ($teamNames[$t1] ?? ('Team #'.$t1)) : null,
                    'team_2_name' => $t2 ? ($teamNames[$t2] ?? ('Team #'.$t2)) : null,
                    'start_hm' => Carbon::parse($slot['start'])->format('H:i'),
                    'end_hm' => Carbon::parse($slot['end'])->format('H:i'),
                ];
            }
        }

        // Build per-team timelines and counts
        $windowMinutes = max(1, $totalMinutes);
        $teamTimeline = [];
        foreach ($teamIds as $tid) { $teamTimeline[$tid] = ['name' => $teamNames[$tid] ?? ('Team #'.$tid), 'segments' => [], 'count' => 0]; }
        foreach ($enriched as $field => $slots) {
            foreach ($slots as $slot) {
                if ($slot['team_1_id'] && $slot['team_2_id']) {
                    foreach (['team_1_id','team_2_id'] as $key) {
                        $tid = $slot[$key];
                        $offsetStart = Carbon::parse($slot['start'])->diffInMinutes($startAt);
                        $offsetEnd = Carbon::parse($slot['end'])->diffInMinutes($startAt);
                        // Clamp values into [0,100] to ensure visibility within bar
                        $left = max(0, min(100, 100 * ($offsetStart / $windowMinutes)));
                        $width = max(0, min(100 - $left, 100 * (($offsetEnd - $offsetStart) / $windowMinutes)));
                        $teamTimeline[$tid]['segments'][] = [
                            'left_pct' => $left,
                            'width_pct' => $width,
                        ];
                        $teamTimeline[$tid]['count']++;
                    }
                }
            }
        }

        // Build flat, ordered slot starts (unique by start time across all fields)
        $slotStarts = collect($enriched)
            ->flatMap(fn($slots) => collect($slots)->pluck('start_hm','start'))
            ->mapWithKeys(fn($hm, $start) => [$start => $hm])
            ->sortKeys()
            ->all();

        // Build team occupancy matrix: teamId => [ start => true (plays) | false (break) ]
        $teamMatrix = [];
        foreach ($teamIds as $tid) {
            $teamMatrix[$tid] = [];
            foreach ($slotStarts as $start => $hm) {
                $teamMatrix[$tid][$start] = false;
            }
        }
        foreach ($enriched as $field => $slots) {
            foreach ($slots as $slot) {
                if ($slot['team_1_id'] && isset($teamMatrix[$slot['team_1_id']][$slot['start']])) {
                    $teamMatrix[$slot['team_1_id']][$slot['start']] = true;
                }
                if ($slot['team_2_id'] && isset($teamMatrix[$slot['team_2_id']][$slot['start']])) {
                    $teamMatrix[$slot['team_2_id']][$slot['start']] = true;
                }
            }
        }

        $result = [
            'summary' => [
                'number_of_fields' => $fields,
                'available_hours' => $hours,
                'match_length_minutes' => $match,
                'buffer_minutes' => $buffer,
                'slots_per_field' => $slotsPerFieldCount,
                'total_slots' => $totalSlots,
                'constraints' => [
                    'max_consecutive_matches' => (int)$data['max_consecutive_matches'],
                    'max_idle_breaks' => (int)$data['max_idle_breaks'],
                ],
                'window' => [
                    'start' => $startAt->toDateTimeString(),
                    'end' => $endAt->toDateTimeString(),
                ],
            ],
            'schedule' => $enriched,
            'teams' => $teamTimeline,
            'slot_starts' => $slotStarts,
            'team_matrix' => $teamMatrix,
        ];

        return view('dashboard.generateSchedule', [
            'defaults' => $data,
            'numberOfFields' => $fields,
            'availableHours' => $hours,
            'result' => $result,
        ]);
    }
    private function roundRobinPairs(array $teams): array
    {
        $t = $teams;
        if (count($t) < 2) return [];
        if (count($t) % 2 === 1) { $t[] = null; }
        $n = count($t);
        $half = $n / 2;
        $home = array_slice($t, 0, $half);
        $away = array_slice($t, $half);
        $pairs = [];
        for ($round = 0; $round < $n - 1; $round++) {
            for ($i = 0; $i < $half; $i++) {
                $a = $home[$i];
                $b = $away[$half - 1 - $i];
                if ($a && $b) $pairs[] = [$a, $b];
            }
            $pivot = array_shift($home);
            $moved = array_pop($away);
            array_unshift($away, $pivot);
            array_push($home, $moved);
        }
        return $pairs;
    }
}
