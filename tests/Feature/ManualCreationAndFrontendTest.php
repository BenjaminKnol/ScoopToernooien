<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualCreationAndFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_player_manually(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $team = Team::create(['name' => 'Lions']);

        $resp = $this->actingAs($admin)->post(route('players.store'), [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => 'jane@example.com',
            'team_id' => $team->id,
            'gender' => 'D',
            'team_code' => 'D1',
        ]);
        $resp->assertRedirect(route('players.index'));

        $this->assertDatabaseHas('players', [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => 'jane@example.com',
            'team_id' => $team->id,
            'gender' => 'D',
            'team_code' => 'D1',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
        ]);
    }

    public function test_my_team_page_contains_score_inputs_for_reporting(): void
    {
        $team = Team::create(['name' => 'Sharks']);
        $team2 = Team::create(['name' => 'Dolphins']);
        $user = User::factory()->create(['is_admin' => false]);
        Game::factory()->create([
            'team_1_id' => $team->id,
            'team_2_id' => $team2->id,
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->subMinutes(5),
            'field' => 0,
        ]);
        Player::create([
            'firstName' => 'P',
            'lastName' => 'L',
            'email' => $user->email,
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('my-team'))
            ->assertOk()
            ->assertSee('Report result')
            ->assertSeeHtml('name="team_1_score"')
            ->assertSeeHtml('name="team_2_score"');
    }

    public function test_admin_dashboard_shows_overview_and_links_to_management_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Team::create(['name' => 'A']);
        Team::create(['name' => 'B']);
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Games')
            ->assertSee('Teams')
            ->assertSee('Players')
            ->assertSee(route('teams.index'), false)
            ->assertSee(route('games.index'), false)
            ->assertSee(route('players.index'), false);

        $this->actingAs($admin)
            ->get(route('teams.index'))
            ->assertOk()
            ->assertSee('Create Team');

        $this->actingAs($admin)
            ->get(route('games.index'))
            ->assertOk()
            ->assertSee('Create Game');

        $this->actingAs($admin)
            ->get(route('players.index'))
            ->assertOk()
            ->assertSee('Manual player creation');
    }
}
