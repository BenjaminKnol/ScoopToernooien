<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamAssignmentController extends Controller
{
    public function create()
    {
        $teamsCount = Team::count();
        $playersCount = Player::count();
        $defaultTarget = $teamsCount > 0 ? (int)ceil($playersCount / max(1, $teamsCount)) : 0;

        $defaults = [
            'target_team_size' => $defaultTarget,
            'gender_weight' => 0.3, // additional weight for males (H)
            'code_weight' => 0.8,   // weight for skill derived from team_code
            'max_team_size_variance' => 1,
            'reassign_existing' => false,
        ];

        return view('dashboard.autoAssignTeams', [
            'defaults' => $defaults,
            'teamsCount' => $teamsCount,
            'playersCount' => $playersCount,
            'result' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target_team_size' => ['required','integer','min:1','max:1000'],
            'gender_weight' => ['required','numeric','min:0','max:10'],
            'code_weight' => ['required','numeric','min:0','max:10'],
            'max_team_size_variance' => ['required','integer','min:0','max:10'],
            'reassign_existing' => ['nullable','boolean'],
            'apply' => ['nullable','boolean'],
        ]);

        $teams = Team::orderBy('name')->get(['id','name']);
        $teamNameById = $teams->pluck('name','id')->all();
        if ($teams->count() === 0) {
            return back()->withErrors(['teams' => __('No teams available to assign players to.')]);
        }

        $target = (int)$data['target_team_size'];
        $variance = (int)$data['max_team_size_variance'];
        $genderW = (float)$data['gender_weight'];
        $codeW = (float)$data['code_weight'];
        $reassign = (bool)($data['reassign_existing'] ?? false);

        // Choose the player set
        $playersQuery = Player::query();
        if (!$reassign) {
            $playersQuery->whereNull('team_id');
        }
        $players = $playersQuery->get(['id','firstName','lastName','gender','team_code','team_id']);
        if ($players->count() === 0) {
            return back()->withErrors(['players' => __('No players to assign with the current selection.')]);
        }

        // Build score for each player
        $scoreFor = function($gender, $teamCode) use ($genderW, $codeW): float {
            $genderBonus = ($gender === 'H') ? $genderW : 0.0; // men tend to be stronger
            $skill = 0.0;
            if (is_string($teamCode) && preg_match('/^([HD])(\d+)$/i', $teamCode, $m)) {
                $n = (int)$m[2];
                // Lower number means stronger (e.g., 1 strongest). Normalize so differences shrink with higher numbers.
                // Example mapping: strength = max(0, 10 - n), then apply sqrt to compress large gaps.
                $base = max(0, 10 - $n); // 9..0 when n=1..10
                $skill = sqrt($base) * $codeW; // diminishing differences between H1 and H6
            }
            return $skill + $genderBonus;
        };

        $playersArr = [];
        foreach ($players as $p) {
            $playersArr[] = [
                'id' => $p->id,
                'name' => $p->firstName.' '.$p->lastName,
                'score' => $scoreFor($p->gender, $p->team_code),
                'team_code' => $p->team_code,
                'current_team_id' => $p->team_id,
            ];
        }

        // Sort players by score descending to place strongest first (greedy load balancing)
        usort($playersArr, function($a, $b) {
            if ($a['score'] === $b['score']) return 0;
            return $a['score'] > $b['score'] ? -1 : 1;
        });

        // Initialize team aggregates
        $teamAgg = [];
        foreach ($teams as $t) {
            $teamAgg[$t->id] = [
                'name' => $t->name,
                'count' => 0,
                'score' => 0.0,
                'members' => [],
            ];
        }

        // If reassigning, start from empty; otherwise, load current team counts/scores as baseline constraints
        if (!$reassign) {
            $currentAssigned = Player::whereNotNull('team_id')->get(['id','team_id','gender','team_code','firstName','lastName']);
            foreach ($currentAssigned as $p) {
                if (!isset($teamAgg[$p->team_id])) continue;
                $s = $scoreFor($p->gender, $p->team_code);
                $teamAgg[$p->team_id]['count']++;
                $teamAgg[$p->team_id]['score'] += $s;
                $teamAgg[$p->team_id]['members'][] = [
                    'id' => $p->id,
                    'name' => $p->firstName.' '.$p->lastName,
                    'score' => $s,
                    'team_code' => $p->team_code,
                    'locked' => true,
                    'current_team_id' => $p->team_id,
                    'current_team_name' => $teamNameById[$p->team_id] ?? null,
                ];
            }
        }

        $minCap = max(0, $target - $variance);
        $maxCap = $target + $variance;

        // Greedy assignment: for each player, place into team with minimal current score among teams that have capacity left
        foreach ($playersArr as $pl) {
            // Skip if already assigned and not reassigning
            if (!$reassign && $pl['current_team_id']) continue;

            $bestTeamId = null;
            $bestScore = null;
            foreach ($teamAgg as $tid => $agg) {
                // Capacity check
                if ($agg['count'] >= $maxCap) continue;
                // Prefer teams below target, but allow up to maxCap
                $effectiveScore = $agg['score'];
                // Slight nudge to fill smaller teams first when scores equal
                $effectiveScore += ($agg['count'] < $target ? -0.01 * ($target - $agg['count']) : 0);
                if ($bestScore === null || $effectiveScore < $bestScore) {
                    $bestScore = $effectiveScore;
                    $bestTeamId = $tid;
                }
            }
            if ($bestTeamId === null) {
                // If all at maxCap, allow placement to the overall lowest-score team regardless
                foreach ($teamAgg as $tid => $agg) {
                    if ($bestScore === null || $agg['score'] < $bestScore) {
                        $bestScore = $agg['score'];
                        $bestTeamId = $tid;
                    }
                }
            }
            if ($bestTeamId !== null) {
                $teamAgg[$bestTeamId]['count']++;
                $teamAgg[$bestTeamId]['score'] += $pl['score'];
                $teamAgg[$bestTeamId]['members'][] = [
                    'id' => $pl['id'],
                    'name' => $pl['name'],
                    'score' => $pl['score'],
                    'team_code' => $pl['team_code'] ?? null,
                    'locked' => false,
                    'current_team_id' => $pl['current_team_id'],
                    'current_team_name' => $pl['current_team_id'] ? ($teamNameById[$pl['current_team_id']] ?? null) : null,
                ];
                $playersByTeam[$bestTeamId][] = $pl['id'];
            }
        }

        // Do not persist yet: return a preview (the admin must accept to apply)
        // Build a flattened proposal list for applying later
        $proposal = [];
        foreach ($teamAgg as $teamId => $agg) {
            foreach ($agg['members'] as $m) {
                if (!empty($m['locked'])) continue; // skip already assigned when not reassigning
                $proposal[] = ['player_id' => $m['id'], 'team_id' => $teamId];
            }
        }

        // Prepare summary
        $summary = [
            'target' => $target,
            'variance' => $variance,
            'gender_weight' => $genderW,
            'code_weight' => $codeW,
            'reassigned' => $reassign,
            'teams' => [],
        ];
        foreach ($teamAgg as $tid => $agg) {
            $summary['teams'][] = [
                'id' => $tid,
                'name' => $agg['name'],
                'count' => $agg['count'],
                'score' => round($agg['score'], 2),
            ];
        }
        // Include detailed members to render a nice preview table
        $summary['teams_detailed'] = $teamAgg;
        $summary['proposal'] = $proposal;

        return view('dashboard.autoAssignTeams', [
            'defaults' => [
                'target_team_size' => $target,
                'gender_weight' => $genderW,
                'code_weight' => $codeW,
                'max_team_size_variance' => $variance,
                'reassign_existing' => $reassign,
            ],
            'teamsCount' => $teams->count(),
            'playersCount' => Player::count(),
            'result' => $summary,
        ]);
    }

    public function apply(Request $request)
    {
        $data = $request->validate([
            'proposal' => ['required','string'],
            'reassign_existing' => ['nullable','boolean'],
        ]);
        $reassign = (bool)($data['reassign_existing'] ?? false);
        $decoded = json_decode($data['proposal'], true);
        if (!is_array($decoded)) {
            return back()->withErrors(['proposal' => __('Invalid proposal payload.')]);
        }
        // Basic shape validation
        foreach ($decoded as $row) {
            if (!is_array($row) || !isset($row['player_id'], $row['team_id'])) {
                return back()->withErrors(['proposal' => __('Malformed proposal data.')]);
            }
        }

        DB::transaction(function() use ($reassign, $decoded) {
            if ($reassign) {
                Player::query()->update(['team_id' => null]);
            }
            foreach ($decoded as $row) {
                Player::where('id', (int)$row['player_id'])->update(['team_id' => (int)$row['team_id']]);
            }
        });

        return redirect()->route('dashboard')->with('success', __('Team assignments applied successfully.'));
    }
}
