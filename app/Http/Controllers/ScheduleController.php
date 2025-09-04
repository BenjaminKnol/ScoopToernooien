<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function create()
    {
        $fieldsDefault = (int) config('scheduling.number_of_fields', 1);
        $hoursDefault = (float) config('scheduling.available_hours', 4);
        $defaults = [
            'match_length_minutes' => 20,
            'max_consecutive_matches' => 2,
            'max_idle_breaks' => 1,
            'number_of_fields' => $fieldsDefault,
            'available_hours' => $hoursDefault,
        ];

        return view('dashboard.generateSchedule', [
            'defaults' => $defaults,
            'numberOfFields' => $fieldsDefault,
            'availableHours' => $hoursDefault,
            'result' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'match_length_minutes' => ['required','integer','min:5','max:90'],
            'max_consecutive_matches' => ['required','integer','min:1','max:10'],
            'max_idle_breaks' => ['required','integer','min:0','max:10'],
            'number_of_fields' => ['nullable','integer','min:1','max:20'],
            'available_hours' => ['nullable','numeric','min:0.5','max:24'],
        ]);

        $fields = (int) ($data['number_of_fields'] ?? config('scheduling.number_of_fields', 1));
        $hours  = (float) ($data['available_hours'] ?? config('scheduling.available_hours', 4));
        $day    = (string) config('scheduling.tournament_day');
        $start  = (string) config('scheduling.start_time');

        $match = (int) $data['match_length_minutes'];
        $buffer = 5; // fixed buffer between matches
        $slotLength = $match + $buffer;

        $startAt = $day && $start ? Carbon::parse($day.' '.$start) : Carbon::now();
        $totalMinutes = (int) round($hours * 60);
        $endAt = (clone $startAt)->addMinutes($totalMinutes);

        // Build timeslots per field including 5-minute buffer between matches
        $buildSlots = function() use ($fields, $startAt, $endAt, $match, $slotLength) {
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
            return $slotsPerField;
        };

        // A function that performs one randomized schedule attempt and returns full result structures
        $attemptSchedule = function(array $slotsPerField) use ($data, $fields, $hours, $match, $buffer, $startAt, $totalMinutes) {
            $slotsPerFieldCount = array_map(fn($s) => count($s), $slotsPerField);
            $totalSlots = array_sum($slotsPerFieldCount);

            // Build pairings
            $teams = Team::orderBy('name')->get(['id','name']);
            $teamIds = $teams->pluck('id')->all();
            $teamNames = $teams->pluck('name','id');
            $pairings = [];
            if (count($teamIds) > 0) {
                $pairings = $this->roundRobinPairs($teamIds);
            }
            // All slots flattened and shuffled per start to diversify
            $allSlots = [];
            foreach ($slotsPerField as $field => $slots) {
                foreach ($slots as $slot) { $allSlots[] = $slot + ['field' => $field]; }
            }
            usort($allSlots, fn($a, $b) => strcmp($a['start'], $b['start']));

            $enriched = [];
            foreach (array_keys($slotsPerField) as $f) { $enriched[$f] = []; }

            $maxConsec = (int)$data['max_consecutive_matches'];
            $pairQueue = $pairings;
            $consecByTeam = array_fill_keys($teamIds, 0);
            $lastPlayedAtIndex = array_fill_keys($teamIds, null);

            $uniqueStarts = array_values(array_unique(array_map(fn($s) => $s['start'], $allSlots)));
            $slotIndexByStart = array_flip($uniqueStarts);

            $teamsPlayingAtStart = [];
            $opponentsFaced = array_fill_keys($teamIds, []);
            $maxIdle = (int)$data['max_idle_breaks'];
            $breakStreak = array_fill_keys($teamIds, 0);
            $violationsMap = [];
            $lastFieldByTeam = array_fill_keys($teamIds, null);
            // Track recent playing patterns to discourage repeating the same global pattern later
            $recentPatterns = []; // array of hashes of teams playing per start

            // Group slots by start and randomize field order each attempt
            $slotsByStart = [];
            foreach ($allSlots as $slot) { $slotsByStart[$slot['start']][] = $slot; }

            $maxMatchesPerStart = min($fields, intdiv(max(0, count($teamIds)), 2));
            foreach ($uniqueStarts as $startKey) {
                $slotIndex = $slotIndexByStart[$startKey];
                if (!isset($teamsPlayingAtStart[$startKey])) { $teamsPlayingAtStart[$startKey] = []; }

                $allowedThisStart = $maxMatchesPerStart;
                // Intentionally leave one field empty every 3rd start (and also the very first) to avoid global re-alignment
                $isIntentionalEmpty = ($slotIndex % 3 === 0) || ($slotIndex === 0);
                if ($isIntentionalEmpty && $maxMatchesPerStart > 0) {
                    $allowedThisStart = max(1, $maxMatchesPerStart - 1);
                }
                $placedThisStart = 0;

                $slotsForStart = $slotsByStart[$startKey];
                shuffle($slotsForStart);
                foreach ($slotsForStart as $slot) {
                    $placed = false;
                    if ($placedThisStart >= $allowedThisStart) {
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

                    $candidates = [];
                    $queueCount = count($pairQueue);
                    for ($i = 0; $i < $queueCount; $i++) {
                        [$t1, $t2] = $pairQueue[$i];
                        if ($t1 === $t2) { continue; }
                        if (in_array($t1, $teamsPlayingAtStart[$startKey] ?? [], true) || in_array($t2, $teamsPlayingAtStart[$startKey] ?? [], true)) { continue; }
                        $prevIdx = $slotIndex - 1;
                        $t1Prev = $lastPlayedAtIndex[$t1] !== null && $lastPlayedAtIndex[$t1] === $prevIdx;
                        $t2Prev = $lastPlayedAtIndex[$t2] !== null && $lastPlayedAtIndex[$t2] === $prevIdx;
                        $t1Consec = $t1Prev ? ($consecByTeam[$t1] + 1) : 1;
                        $t2Consec = $t2Prev ? ($consecByTeam[$t2] + 1) : 1;
                        if (!($t1Consec <= $maxConsec && $t2Consec <= $maxConsec)) { continue; }
                        $repeatOpponent = in_array($t2, $opponentsFaced[$t1] ?? [], true) || in_array($t1, $opponentsFaced[$t2] ?? [], true);
                        $score = 0;
                        $score += ($breakStreak[$t1] ?? 0) + ($breakStreak[$t2] ?? 0);
                        if ($repeatOpponent) { $score -= 5; }
                        if (!$t1Prev) { $score += 1; }
                        if (!$t2Prev) { $score += 1; }
                        if ($t1Prev) { $score -= 2; }
                        if ($t2Prev) { $score -= 2; }
                        $sameFieldPenalty = 0;
                        if (($lastFieldByTeam[$t1] ?? null) === $slot['field']) { $sameFieldPenalty += 0.5; }
                        if (($lastFieldByTeam[$t2] ?? null) === $slot['field']) { $sameFieldPenalty += 0.5; }
                        $score -= $sameFieldPenalty;
                        // Penalize recreating recent global playing patterns (look back 3 starts)
                        $patternPenalty = 0;
                        $recentHash = null;
                        if (!empty($recentPatterns)) {
                            $keys = array_keys($recentPatterns);
                            $lastKeys = array_slice($keys, -3);
                            $playingSet = $teamsPlayingAtStart[$startKey] ?? [];
                            $tmp = $playingSet; $tmp[] = $t1; $tmp[] = $t2; sort($tmp);
                            $hash = md5(json_encode($tmp));
                            foreach ($lastKeys as $k) { if ($recentPatterns[$k] === $hash) { $patternPenalty += 1.5; } }
                        }
                        $score -= $patternPenalty;
                        $score += mt_rand(0, 2) / 10.0; // randomness per attempt
                        $candidates[] = compact('i','t1','t2','t1Consec','t2Consec','repeatOpponent') + ['score' => $score];
                    }

                    if (!empty($candidates)) {
                        usort($candidates, function($a, $b) { return $b['score'] <=> $a['score']; });
                        $best = $candidates[0];
                        $i = $best['i'];
                        $t1 = $best['t1'];
                        $t2 = $best['t2'];
                        $t1Consec = $best['t1Consec'];
                        $t2Consec = $best['t2Consec'];
                        $repeatOpponent = $best['repeatOpponent'];

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

                // break streaks update once per start
                $playingNow = $teamsPlayingAtStart[$startKey] ?? [];
                foreach ($teamIds as $tid) {
                    if (in_array($tid, $playingNow, true)) { $breakStreak[$tid] = 0; }
                    else {
                        $breakStreak[$tid]++;
                        if ($lastPlayedAtIndex[$tid] !== null && $breakStreak[$tid] > $maxIdle) {
                            $violationsMap[$startKey][$tid]['over_breaks'] = true;
                        }
                        $consecByTeam[$tid] = 0;
                    }
                }
                // Record pattern hash for this start to discourage repeating later
                $curr = $teamsPlayingAtStart[$startKey] ?? [];
                sort($curr);
                $recentPatterns[$startKey] = md5(json_encode($curr));
            }

            // Unplaced
            $unplaced = count($pairQueue);

            // Build matrices
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
                            $left = max(0, min(100, 100 * ($offsetStart / $windowMinutes)));
                            $width = max(0, min(100 - $left, 100 * (($offsetEnd - $offsetStart) / $windowMinutes)));
                            $teamTimeline[$tid]['segments'][] = ['left_pct' => $left,'width_pct' => $width,];
                            $teamTimeline[$tid]['count']++;
                        }
                    }
                }
            }
            $slotStarts = collect($enriched)
                ->flatMap(fn($slots) => collect($slots)->pluck('start_hm','start'))
                ->mapWithKeys(fn($hm, $start) => [$start => $hm])
                ->sortKeys()
                ->all();

            $teamMatrix = [];
            foreach ($teamIds as $tid) {
                $teamMatrix[$tid] = [];
                foreach ($slotStarts as $start => $hm) { $teamMatrix[$tid][$start] = false; }
            }
            foreach ($enriched as $field => $slots) {
                foreach ($slots as $slot) {
                    if ($slot['team_1_id'] && isset($teamMatrix[$slot['team_1_id']][$slot['start']])) { $teamMatrix[$slot['team_1_id']][$slot['start']] = true; }
                    if ($slot['team_2_id'] && isset($teamMatrix[$slot['team_2_id']][$slot['start']])) { $teamMatrix[$slot['team_2_id']][$slot['start']] = true; }
                }
            }

            // Evaluate break alignment: compute max idle per slot and sum of squares
            $teamsCount = count($teamIds);
            $breaksPerStart = [];
            foreach ($slotStarts as $startKey => $hm) {
                $idle = 0;
                foreach ($teamIds as $tid) { if (!($teamMatrix[$tid][$startKey] ?? false)) { $idle++; } }
                $breaksPerStart[$startKey] = $idle;
            }
            $maxIdleInAnyStart = empty($breaksPerStart) ? 0 : max($breaksPerStart);
            $sumSquares = 0;
            foreach ($breaksPerStart as $val) { $sumSquares += ($val * $val); }

            return compact(
                'slotsPerField','slotsPerFieldCount','totalSlots','enriched','teamTimeline','slotStarts','teamMatrix','unplaced','breaksPerStart','maxIdleInAnyStart','sumSquares','teamsCount'
            );
        };

        // Regeneration loop until breaks don’t align too much
        $maxAttempts = 30;
        $best = null; $bestScore = PHP_INT_MAX; $usedAttempts = 0;
        $slotsPerFieldBase = $buildSlots();
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $usedAttempts = $attempt;
            // Small perturbation: shuffle fields order to change tie-breaking further between attempts
            $slotsPerField = $slotsPerFieldBase;
            // attempt schedule
            $out = $attemptSchedule($slotsPerField);
            // Heuristic acceptance: we want to avoid many teams idle at same time
            $threshold = max(0, intdiv($out['teamsCount'], 2) - 1); // allow at most < ~half idle
            $score = ($out['maxIdleInAnyStart'] * 1000) + $out['sumSquares'] + ($out['unplaced'] * 100000);
            if ($score < $bestScore) { $best = $out; $bestScore = $score; }
            if ($out['maxIdleInAnyStart'] <= $threshold && $out['unplaced'] === 0) {
                $best = $out; // accept satisfactory
                break;
            }
        }
        $out = $best ?? $attemptSchedule($buildSlots());

        // Rebuild flags and field matrices using chosen output to keep existing view structure
        $slotsPerFieldCount = $out['slotsPerFieldCount'];
        $totalSlots = $out['totalSlots'];
        $enriched = $out['enriched'];
        $teamTimeline = $out['teamTimeline'];
        $slotStarts = $out['slotStarts'];
        $teamMatrix = $out['teamMatrix'];
        $unplaced = $out['unplaced'];
        // Build flags and field usage for view (lightweight rebuild)
        $teamIds = Team::orderBy('name')->get(['id'])->pluck('id')->all();
        $teamCellFlags = [];
        foreach ($teamIds as $tid) {
            $teamCellFlags[$tid] = [];
            foreach ($slotStarts as $startKey => $hm) { $teamCellFlags[$tid][$startKey] = []; }
        }
        $fieldMatrix = [];
        $fieldUsageCount = [];
        foreach (array_keys($enriched) as $field) {
            $fieldMatrix[$field] = [];
            $fieldUsageCount[$field] = 0;
            foreach ($slotStarts as $startKey => $hm) { $fieldMatrix[$field][$startKey] = false; }
        }
        foreach ($enriched as $field => $slots) {
            foreach ($slots as $slot) {
                $used = ($slot['team_1_id'] && $slot['team_2_id']);
                if ($used) {
                    $fieldMatrix[$field][$slot['start']] = true;
                    $fieldUsageCount[$field]++;
                }
            }
        }

        // Compute feasibility and suggestions
        $teamsCount = Team::count();
        $requiredMatches = max(0, ($teamsCount * ($teamsCount - 1)) / 2); // simple single round robin combinations
        $capacityMatches = $totalSlots; // each slot hosts 1 match across all fields
        $suggestions = [];
        if ($capacityMatches < $requiredMatches) {
            // Suggest increasing hours or reducing match length
            $neededSlots = (int) ceil($requiredMatches);
            $perFieldSlots = array_sum($slotsPerFieldCount) / max(1, $fields);
            $perFieldSlots = max(1, (int) round($perFieldSlots));
            // compute suggested hours at current match+buffer
            $minutesPerSlot = max(1, $match + $buffer);
            $slotsNeededPerField = (int) ceil($neededSlots / max(1, $fields));
            $minutesNeeded = $slotsNeededPerField * $minutesPerSlot;
            $hoursSuggested = round($minutesNeeded / 60, 2);
            $suggestions[] = __('Increase AVAILABLE_HOURS to ~:h to fit all pairings (now :now h).', ['h' => $hoursSuggested, 'now' => $hours]);
            // or suggest shorter matches
            if ($hours > 0) {
                $totalAvailableMinutes = (int) round($hours * 60);
                $slotsPerFieldPossible = (int) floor($totalAvailableMinutes / $minutesPerSlot);
                if ($slotsPerFieldPossible * $fields < $neededSlots && $match > 5) {
                    $targetSlotsPerField = (int) ceil($neededSlots / max(1, $fields));
                    $targetMinutesPerSlot = (int) floor($totalAvailableMinutes / max(1, $targetSlotsPerField));
                    $matchLenSuggestion = max(5, $targetMinutesPerSlot - $buffer);
                    if ($matchLenSuggestion < $match) {
                        $suggestions[] = __('Reduce match length to ~:m min (buffer :b) to fit all pairings.', ['m' => $matchLenSuggestion, 'b' => $buffer]);
                    }
                }
            }
        }
        // Suggest reducing simultaneous empties if too many teams idle at once
        if (($out['maxIdleInAnyStart'] ?? 0) >= max(1, floor($teamsCount * 0.75))) {
            $suggestions[] = __('Too many teams idle simultaneously. Consider reducing NUMBER_OF_FIELDS or increasing AVAILABLE_HOURS to stagger breaks.');
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
                'regeneration' => [
                    'attempts_used' => $usedAttempts,
                    'max_idle_in_any_slot' => $out['maxIdleInAnyStart'] ?? null,
                    'intentional_empty_rule' => '1 field left empty every 3rd start (capacity permitting)',
                ],
                'violations' => [
                    'unplaced_pairings' => $unplaced ?? 0,
                ],
                'suggestions' => $suggestions,
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

    public function apply(Request $request)
    {
        $data = $request->validate([
            'games' => ['required', 'string'],
            'clear_existing' => ['nullable', 'boolean'],
        ]);
        $clear = (bool)($data['clear_existing'] ?? false);
        $decoded = json_decode($data['games'], true);
        if (!is_array($decoded)) {
            return back()->withErrors(['games' => __('Invalid games payload.')]);
        }
        // Basic shape validation and normalize field to int
        $toInsert = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) { continue; }
            $t1 = (int)($row['team_1_id'] ?? 0);
            $t2 = (int)($row['team_2_id'] ?? 0);
            $start = (string)($row['start_time'] ?? '');
            $end = (string)($row['end_time'] ?? '');
            $field = (int)($row['field'] ?? 0);
            if ($t1 && $t2 && $start && $end) {
                $toInsert[] = [
                    'team_1_id' => $t1,
                    'team_2_id' => $t2,
                    'start_time' => $start,
                    'end_time' => $end,
                    'field' => $field,
                ];
            }
        }
        if (empty($toInsert)) {
            return back()->withErrors(['games' => __('There are no matches to apply.')]);
        }

        DB::transaction(function () use ($clear, $toInsert) {
            if ($clear) {
                // Remove all existing games before inserting the new schedule
                \App\Models\Game::query()->delete();
            }
            // Bulk insert; if needed, chunk to avoid huge payloads
            foreach (array_chunk($toInsert, 500) as $chunk) {
                \App\Models\Game::insert($chunk);
            }
        });

        return redirect()->route('dashboard')->with('success', __('Schedule applied and saved successfully.'));
    }
}
