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
        // Build a global ordered list of slots across all fields by start time for constraint-aware assignment
        $allSlots = [];
        foreach ($slotsPerField as $field => $slots) {
            foreach ($slots as $slot) {
                $allSlots[] = $slot + ['field' => $field];
            }
        }
        usort($allSlots, fn($a, $b) => strcmp($a['start'], $b['start']));

        // Prepare enriched structure keyed by field
        $enriched = [];
        foreach (array_keys($slotsPerField) as $f) { $enriched[$f] = []; }

        // Constraint-aware greedy assignment to respect max_consecutive_matches per team
        $maxConsec = (int)$data['max_consecutive_matches'];
        $pairQueue = $pairings; // queue of remaining pairings
        $consecByTeam = array_fill_keys($teamIds, 0);
        $lastPlayedAtIndex = array_fill_keys($teamIds, null);
        $violations = 0;

        // Map slot start to an index to track adjacency per chronological slot
        $uniqueStarts = array_values(array_unique(array_map(fn($s) => $s['start'], $allSlots)));
        $slotIndexByStart = array_flip($uniqueStarts);

        // Track who is already playing per start time to prevent double-booking per team
        $teamsPlayingAtStart = [];
        $opponentsFaced = array_fill_keys($teamIds, []);
        $maxIdle = (int)$data['max_idle_breaks'];
        $breakStreak = array_fill_keys($teamIds, 0);
        $violationsMap = []; // [start=>[teamId=>[types]]]
        $lastFieldByTeam = array_fill_keys($teamIds, null);

        // Group slots by start to update streaks once per chronological start
        $slotsByStart = [];
        foreach ($allSlots as $slot) {
            $slotsByStart[$slot['start']][] = $slot;
        }
        $maxMatchesPerStart = min($fields, intdiv(max(0, count($teamIds)), 2));
        foreach ($uniqueStarts as $startKey) {
            $slotIndex = $slotIndexByStart[$startKey];
            if (!isset($teamsPlayingAtStart[$startKey])) { $teamsPlayingAtStart[$startKey] = []; }

            // For the very first start, intentionally leave 1 slot empty (if possible) to de-sync breaks later
            $allowedThisStart = $maxMatchesPerStart;
            if ($slotIndex === 0 && $maxMatchesPerStart > 0) {
                $allowedThisStart = max(1, $maxMatchesPerStart - 1);
            }
            $placedThisStart = 0;

            // Shuffle field order for this start to avoid synchronized empties
            $slotsForStart = $slotsByStart[$startKey];
            shuffle($slotsForStart);
            foreach ($slotsForStart as $slot) {
                $placed = false;
                if ($placedThisStart >= $allowedThisStart) {
                    // Force remaining fields at this start to be empty to seed staggering
                    $field = $slot['field'];
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

                // Build candidate list with scoring to avoid instant repeats and create breaks
                $candidates = [];
                $queueCount = count($pairQueue);
                for ($i = 0; $i < $queueCount; $i++) {
                    [$t1, $t2] = $pairQueue[$i];
                    if ($t1 === $t2) { continue; } // no self-play
                    if (in_array($t1, $teamsPlayingAtStart[$startKey], true) || in_array($t2, $teamsPlayingAtStart[$startKey], true)) {
                        continue; // already playing this start
                    }
                    // consecutive computation relative to previous start
                    $prevIdx = $slotIndex - 1;
                    $t1Prev = $lastPlayedAtIndex[$t1] !== null && $lastPlayedAtIndex[$t1] === $prevIdx;
                    $t2Prev = $lastPlayedAtIndex[$t2] !== null && $lastPlayedAtIndex[$t2] === $prevIdx;
                    $t1Consec = $t1Prev ? ($consecByTeam[$t1] + 1) : 1;
                    $t2Consec = $t2Prev ? ($consecByTeam[$t2] + 1) : 1;
                    if (!($t1Consec <= $maxConsec && $t2Consec <= $maxConsec)) {
                        continue; // hard block
                    }
                    // prefer non-repeats until all opponents met
                    $repeatOpponent = in_array($t2, $opponentsFaced[$t1] ?? [], true) || in_array($t1, $opponentsFaced[$t2] ?? [], true);
                    // scoring: higher is better
                    $score = 0;
                    $score += ($breakStreak[$t1] ?? 0) + ($breakStreak[$t2] ?? 0);
                    if ($repeatOpponent) { $score -= 5; }
                    if (!$t1Prev) { $score += 1; }
                    if (!$t2Prev) { $score += 1; }
                    // discourage consecutive back-to-back usage when alternatives exist
                    if ($t1Prev) { $score -= 2; }
                    if ($t2Prev) { $score -= 2; }
                    // discourage using the same field as last time (soft)
                    $sameFieldPenalty = 0;
                    if (($lastFieldByTeam[$t1] ?? null) === $slot['field']) { $sameFieldPenalty += 0.5; }
                    if (($lastFieldByTeam[$t2] ?? null) === $slot['field']) { $sameFieldPenalty += 0.5; }
                    $score -= $sameFieldPenalty;
                    $score += mt_rand(0, 2) / 10.0;
                    $candidates[] = compact('i','t1','t2','t1Consec','t2Consec','repeatOpponent') + ['score' => $score];
                }

                if (!empty($candidates)) {
                    usort($candidates, function($a, $b) {
                        if ($a['score'] === $b['score']) return 0;
                        return ($a['score'] > $b['score']) ? -1 : 1;
                    });
                    $best = $candidates[0];
                    $i = $best['i'];
                    $t1 = $best['t1'];
                    $t2 = $best['t2'];
                    $t1Consec = $best['t1Consec'];
                    $t2Consec = $best['t2Consec'];
                    $repeatOpponent = $best['repeatOpponent'];

                    // Place this pairing here
                    $placed = true;
                    array_splice($pairQueue, $i, 1);

                    $field = $slot['field'];
                    $enriched[$field][] = $slot + [
                        'team_1_id' => $t1,
                        'team_2_id' => $t2,
                        'team_1_name' => $t1 ? ($teamNames[$t1] ?? ('Team #'.$t1)) : null,
                        'team_2_name' => $t2 ? ($teamNames[$t2] ?? ('Team #'.$t2)) : null,
                        'start_hm' => Carbon::parse($slot['start'])->format('H:i'),
                        'end_hm' => Carbon::parse($slot['end'])->format('H:i'),
                        'violations' => [ 'repeat_opponent' => $repeatOpponent ],
                    ];

                    // mark played at this start; update last played index now
                    $teamsPlayingAtStart[$startKey][] = $t1;
                    $teamsPlayingAtStart[$startKey][] = $t2;
                    $lastPlayedAtIndex[$t1] = $slotIndex;
                    $lastPlayedAtIndex[$t2] = $slotIndex;
                    $consecByTeam[$t1] = $t1Consec;
                    $consecByTeam[$t2] = $t2Consec;
                    $opponentsFaced[$t1][] = $t2;
                    $opponentsFaced[$t2][] = $t1;
                    $lastFieldByTeam[$t1] = $slot['field'];
                    $lastFieldByTeam[$t2] = $slot['field'];
                    $placedThisStart++;
                }

                if (!$placed) {
                    $field = $slot['field'];
                    $enriched[$field][] = $slot + [
                        'team_1_id' => null,
                        'team_2_id' => null,
                        'team_1_name' => null,
                        'team_2_name' => null,
                        'start_hm' => Carbon::parse($slot['start'])->format('H:i'),
                        'end_hm' => Carbon::parse($slot['end'])->format('H:i'),
                    ];
                }
            }

            // After assigning all fields for this start, update break streaks once
            $playingNow = $teamsPlayingAtStart[$startKey] ?? [];
            foreach ($teamIds as $tid) {
                if (in_array($tid, $playingNow, true)) {
                    $breakStreak[$tid] = 0;
                } else {
                    $breakStreak[$tid]++;
                    // Only flag over-breaks after the team has played at least once
                    if ($lastPlayedAtIndex[$tid] !== null && $breakStreak[$tid] > $maxIdle) {
                        $violationsMap[$startKey][$tid]['over_breaks'] = true;
                    }
                    $consecByTeam[$tid] = 0; // a break resets consecutive matches
                }
            }
        }

        // If any pairings remain, they couldn’t be placed due to constraints/capacity
        $unplaced = count($pairQueue);
        if ($unplaced > 0) { $violations += $unplaced; }

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

        // Build team occupancy matrix and per-cell violation flags
        $teamMatrix = [];
        $teamCellFlags = [];
        foreach ($teamIds as $tid) {
            $teamMatrix[$tid] = [];
            $teamCellFlags[$tid] = [];
            foreach ($slotStarts as $start => $hm) {
                $teamMatrix[$tid][$start] = false;
                $teamCellFlags[$tid][$start] = [];
            }
        }
        // Field utilization matrix: field => [start => true (used) | false (empty)]
        $fieldMatrix = [];
        $fieldUsageCount = [];
        foreach (array_keys($enriched) as $field) {
            $fieldMatrix[$field] = [];
            $fieldUsageCount[$field] = 0;
            foreach ($slotStarts as $start => $hm) {
                $fieldMatrix[$field][$start] = false;
            }
        }
        foreach ($enriched as $field => $slots) {
            foreach ($slots as $slot) {
                $used = ($slot['team_1_id'] && $slot['team_2_id']);
                if ($slot['team_1_id'] && isset($teamMatrix[$slot['team_1_id']][$slot['start']])) {
                    $teamMatrix[$slot['team_1_id']][$slot['start']] = true;
                    if (!empty($slot['violations']['repeat_opponent'])) {
                        $teamCellFlags[$slot['team_1_id']][$slot['start']]['repeat_opponent'] = true;
                    }
                }
                if ($slot['team_2_id'] && isset($teamMatrix[$slot['team_2_id']][$slot['start']])) {
                    $teamMatrix[$slot['team_2_id']][$slot['start']] = true;
                    if (!empty($slot['violations']['repeat_opponent'])) {
                        $teamCellFlags[$slot['team_2_id']][$slot['start']]['repeat_opponent'] = true;
                    }
                }
                if (isset($fieldMatrix[$field][$slot['start']]) && $used) {
                    if (!$fieldMatrix[$field][$slot['start']]) {
                        $fieldMatrix[$field][$slot['start']] = true;
                        $fieldUsageCount[$field]++;
                    }
                }
            }
        }
        // Apply over-breaks flags from violationsMap
        foreach ($violationsMap as $start => $byTeam) {
            foreach ($byTeam as $tid => $types) {
                if (!empty($types['over_breaks'])) {
                    $teamCellFlags[$tid][$start]['over_breaks'] = true;
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
            'violations' => [
                'unplaced_pairings' => $unplaced ?? 0,
            ],
            ],
            'schedule' => $enriched,
            'teams' => $teamTimeline,
            'slot_starts' => $slotStarts,
            'team_matrix' => $teamMatrix,
                        'team_cell_flags' => $teamCellFlags,
            'field_matrix' => $fieldMatrix,
            'field_usage_count' => $fieldUsageCount,
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
        // Return each unordered pair exactly once (simple combinations),
        // which avoids duplicates and lets the scheduler handle ordering.
        $ids = array_values(array_filter($teams, fn($t) => !is_null($t)));
        $n = count($ids);
        $pairs = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $pairs[] = [$ids[$i], $ids[$j]];
            }
        }
        return $pairs;
    }
}
