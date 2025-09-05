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
        $resp->assertRedirect(route('dashboard'));

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

    public function test_my_team_page_contains_strict_score_input_pattern(): void
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
            ->assertSeeHtml('pattern="\\\d+-\\\d+"');
    }

    public function test_admin_dashboard_shows_manual_creation_forms(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Team::create(['name' => 'A']);
        Team::create(['name' => 'B']);
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Games')
            ->assertSee('Create Game')
            ->assertSee('Teams')
            ->assertSee('Create Team')
            ->assertSee('Players')
            ->assertSee('Manual player creation');
    }
}
