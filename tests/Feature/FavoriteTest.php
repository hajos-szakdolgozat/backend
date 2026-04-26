<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_a_boat_to_favorites(): void
    {
        $user = User::factory()->create();
        $boat = Boat::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->postJson("/api/favorites/{$boat->id}");

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Boat added to favorites successfully.']);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'boat_id' => $boat->id,
        ]);
    }

    public function test_authenticated_user_can_remove_a_boat_from_favorites(): void
    {
        $user = User::factory()->create();
        $boat = Boat::factory()->create(['is_active' => true]);

        Favorite::create([
            'user_id' => $user->id,
            'boat_id' => $boat->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/favorites/{$boat->id}");

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Boat removed from favorites.']);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'boat_id' => $boat->id,
        ]);
    }

    public function test_removing_a_non_existing_favorite_returns_404(): void
    {
        $user = User::factory()->create();
        $boat = Boat::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/favorites/{$boat->id}");

        $response->assertNotFound()
            ->assertJsonFragment(['message' => 'Favorite not found.']);
    }

    public function test_unauthenticated_user_cannot_add_favorite(): void
    {
        $boat = Boat::factory()->create();

        $this->postJson("/api/favorites/{$boat->id}")->assertUnauthorized();
    }

    public function test_my_favorites_returns_only_current_users_boats(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $boat1 = Boat::factory()->create(['is_active' => true]);
        $boat2 = Boat::factory()->create(['is_active' => true]);

        Favorite::create(['user_id' => $user1->id, 'boat_id' => $boat1->id]);
        Favorite::create(['user_id' => $user2->id, 'boat_id' => $boat2->id]);

        $response = $this->actingAs($user1)->getJson('/api/favorites/me');

        $response->assertOk();

        $boatIds = collect($response->json())->pluck('id')->toArray();
        $this->assertContains($boat1->id, $boatIds);
        $this->assertNotContains($boat2->id, $boatIds);
    }
}
