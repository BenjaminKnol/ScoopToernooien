<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManualCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_create_team_and_player_via_dashboard_forms(): void
    {
        $admin = $this->admin();

        // Create team
        $this->actingAs($admin)
            ->post(route('teams.store'), [
                'name' => 'Tigers',
                'points' => 0,
                'costume_rating' => 5,
            ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('teams', ['name' => 'Tigers', 'costume_rating' => 5]);
    }

    public function test_admin_can_create_update_delete_game_manually(): void
    {
        $admin = $this->admin();
        $t1 = Team::create(['name' => 'Alpha']);
        $t2 = Team::create(['name' => 'Beta']);

        // Create
        $resp = $this->actingAs($admin)->post(route('games.store'), [
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'team_1_id' => $t1->id,
            'team_2_id' => $t2->id,
            'field' => 1,
        ]);
        $resp->assertRedirect('dashboard');
        $game = Game::first();
        $this->assertNotNull($game);

        // Update
        $this->actingAs($admin)->put(route('games.update', $game), [
            'field' => 2,
        ])->assertRedirect('dashboard');
        $this->assertEquals(2, $game->fresh()->field);

        // Delete
        $this->actingAs($admin)->delete(route('games.destroy', $game))
            ->assertRedirect('dashboard');
        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    public function test_non_admin_blocked_by_middleware(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->post(route('teams.store'), ['name' => 'X'])
            ->assertRedirect(route('home'));
    }
}
