<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GameResultApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function createTeamsAndGame(): array
    {
        $team1 = Team::create(['name' => 'Alpha', 'points' => 0]);
        $team2 = Team::create(['name' => 'Beta', 'points' => 0]);
        $start = (string)Carbon::now()->subMinutes(10);
        $end = (string)Carbon::now()->subMinutes(5);
        $game = Game::create([
            'start_time' => $start,
            'end_time' => $end,
            'field' => 0,
            'team_1_id' => $team1->id,
            'team_2_id' => $team2->id,
        ]);
        return [$team1, $team2, $game];
    }

    private function createPlayerUser(Team $team, bool $admin = false): User
    {
        $user = User::factory()->create(['is_admin' => $admin]);
        Player::create([
            'firstName' => 'P',
            'lastName' => 'L',
            'email' => $user->email,
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);
        return $user;
    }

    public function test_validation_rejects_bad_format(): void
    {
        [$team1, $team2, $game] = $this->createTeamsAndGame();
        $user = $this->createPlayerUser($team1);

        $this->actingAs($user)
            ->post(route('team.games.report', $game), ['score' => '1 - 2'])
            ->assertSessionHasErrors('score');

        $this->actingAs($user)
            ->post(route('team.games.report', $game), ['score' => 'a-b'])
            ->assertSessionHasErrors('score');

        $this->actingAs($user)
            ->post(route('team.games.report', $game), ['score' => '1-2-3'])
            ->assertSessionHasErrors('score');
    }

    public function test_matching_submissions_are_auto_accepted(): void
    {
        [$team1, $team2, $game] = $this->createTeamsAndGame();
        $u1 = $this->createPlayerUser($team1);
        $u2 = $this->createPlayerUser($team2);

        $this->actingAs($u1)->post(route('team.games.report', $game), ['score' => '2-1'])
            ->assertSessionHas('success');
        $game->refresh();
        $this->assertSame('pending', $game->status);
        $this->assertSame('2-1', $game->team_1_submission);
        $this->assertNull($game->accepted_outcome);

        $this->actingAs($u2)->post(route('team.games.report', $game), ['score' => '2-1'])
            ->assertSessionHas('success');
        $game->refresh();
        $this->assertSame('accepted', $game->status);
        $this->assertSame('2-1', $game->accepted_outcome);
        $this->assertSame(1, $game->points_applied);

        // Points: team1 should have +3
        $this->assertSame(3, $team1->fresh()->points);
        $this->assertSame(0, $team2->fresh()->points);
    }

    public function test_conflicting_submissions_require_admin_approval(): void
    {
        [$team1, $team2, $game] = $this->createTeamsAndGame();
        $u1 = $this->createPlayerUser($team1);
        $u2 = $this->createPlayerUser($team2);

        $this->actingAs($u1)->post(route('team.games.report', $game), ['score' => '0-0']);
        $this->actingAs($u2)->post(route('team.games.report', $game), ['score' => '1-0']);
        $game->refresh();
        $this->assertSame('conflict', $game->status);
        $this->assertNull($game->accepted_outcome);
        $this->assertSame(0, $game->points_applied);

        // Admin approves one score (override)
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->post(route('admin.games.approve', $game), ['score' => '1-0'])
            ->assertSessionHas('success');

        $game->refresh();
        $this->assertSame('accepted', $game->status);
        $this->assertSame('1-0', $game->accepted_outcome);
        $this->assertSame(1, $game->points_applied);
        $this->assertSame(3, $team1->fresh()->points);
        $this->assertSame(0, $team2->fresh()->points);
    }

    public function test_only_admin_can_override_accepted_outcome(): void
    {
        [$team1, $team2, $game] = $this->createTeamsAndGame();
        $u1 = $this->createPlayerUser($team1);
        $u2 = $this->createPlayerUser($team2);

        // Auto-accept via two matching submissions
        $this->actingAs($u1)->post(route('team.games.report', $game), ['score' => '1-1']);
        $this->actingAs($u2)->post(route('team.games.report', $game), ['score' => '1-1']);
        $game->refresh();
        $this->assertSame('accepted', $game->status);
        $this->assertSame(1, $team1->fresh()->points);
        $this->assertSame(1, $team2->fresh()->points);

        // Non-admin attempt (expect redirect by middleware to home)
        $this->actingAs($u1)
            ->post(route('admin.games.approve', $game), ['score' => '2-1'])
            ->assertRedirect(route('home'));

        // Admin overrides: revert draw points and apply win points
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->post(route('admin.games.approve', $game), ['score' => '2-1'])
            ->assertSessionHas('success');

        $game->refresh();
        $this->assertSame('accepted', $game->status);
        $this->assertSame('2-1', $game->accepted_outcome);
        // Points should now be 3 for team1, 0 for team2 (1-1 draw reverted first)
        $this->assertSame(3, $team1->fresh()->points);
        $this->assertSame(0, $team2->fresh()->points);
    }
}
