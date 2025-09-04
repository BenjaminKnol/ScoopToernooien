<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlayerAdminController extends Controller
{
    /**
     * Import players from a CSV file and create corresponding User accounts.
     * Expected headers (case-insensitive, flexible): firstName, lastName (or secondName), email. Optional: Team (e.g., H1/D3) — gender inferred from Team.
     */
    public function import(Request $request)
    {
        $data = $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $data['csv'];
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->withErrors(['csv' => __('Unable to read uploaded CSV file.')]);
        }

        $created = 0; $updated = 0; $skipped = 0;

        DB::beginTransaction();
        try {
            $header = fgetcsv($handle);
            if (!$header) {
                throw new \RuntimeException('CSV is empty or unreadable.');
            }
            // Normalize headers
            $map = [];
            foreach ($header as $i => $h) {
                $key = Str::of($h)->lower()->replace(' ', '')->replace('-', '');
                $map[$i] = (string)$key;
            }

            // Helper to get column by possible names
            $findIndex = function(array $candidates) use ($map) {
                foreach ($map as $i => $name) {
                    foreach ($candidates as $c) {
                        if (str_contains($name, $c)) {
                            return $i;
                        }
                    }
                }
                return null;
            };

            $idxFirst = $findIndex(['firstname','first_name','first','voornaam']);
            $idxLast  = $findIndex(['lastname','last_name','secondname','second','surname','achternaam']);
            $idxEmail = $findIndex(['email','e_mail']);

            if ($idxFirst === null || $idxLast === null || $idxEmail === null) {
                throw new \RuntimeException('CSV must contain First Name, Last Name, and Email columns.');
            }

            $idxGender = $findIndex(['gender','geslacht']);
            $idxTeamCode = $findIndex(['teamcode','team_code','team','groep']);

            while (($row = fgetcsv($handle)) !== false) {
                $first = trim($row[$idxFirst] ?? '');
                $last  = trim($row[$idxLast] ?? '');
                $email = trim($row[$idxEmail] ?? '');

                // Read team code from Team column (or aliases) and infer gender from it
                $teamCodeRaw = $idxTeamCode !== null ? strtoupper(trim($row[$idxTeamCode] ?? '')) : '';
                $teamCode = preg_match('/^[HD][0-9]+$/', $teamCodeRaw) ? $teamCodeRaw : null;

                if ($teamCode) {
                    $gender = $teamCode[0];
                } else {
                    // Fallback: if a separate gender column exists, use it; otherwise leave null
                    $genderRaw = $idxGender !== null ? strtoupper(trim($row[$idxGender] ?? '')) : '';
                    $gender = in_array($genderRaw, ['H','D']) ? $genderRaw : null;
                }

                if ($first === '' || $last === '' || $email === '') {
                    $skipped++;
                    continue;
                }

                // Create or fetch user by email
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $username = Str::studly($first.$last); // FirstNameLastName
                    $user = User::create([
                        'name' => $username,
                        'email' => $email,
                        'password' => $email, // As requested (insecure, event-only)
                        'is_admin' => false,
                    ]);
                    $created++;
                } else {
                    $updated++;
                }
                // Create or update player record
                $player = Player::where('email', $email)->first();
                if (!$player) {
                    $player = Player::create([
                        'firstName' => $first,
                        'lastName' => $last,
                        'email' => $email,
                        'user_id' => $user->id,
                        'team_id' => null,
                        'gender' => $gender,
                        'team_code' => $teamCode,
                    ]);
                } else {
                    $player->update([
                        'firstName' => $first ?: $player->firstName,
                        'lastName' => $last ?: $player->lastName,
                        'user_id' => $user->id,
                        'gender' => $gender ?: $player->gender,
                        'team_code' => $teamCode ?: $player->team_code,
                    ]);
                }
            }

            fclose($handle);
            DB::commit();
        } catch (\Throwable $e) {
            if (is_resource($handle)) fclose($handle);
            DB::rollBack();
            return back()->withErrors(['csv' => $e->getMessage()]);
        }

        return redirect()->route('dashboard')->with('success', __("Imported players. Users created: :c, existing updated: :u, rows skipped: :s", ['c' => $created, 'u' => $updated, 's' => $skipped]));
    }

    /** Assign/Update player's team */
    public function update(Request $request, Player $player)
    {
        $data = $request->validate([
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
        ]);

        $player->update([
            'team_id' => $data['team_id'] ?? null,
        ]);

        // Optionally update Team.number_of_players counts elsewhere later

        return redirect()->route('dashboard')->with('success', __('Player updated successfully.'));
    }
}
