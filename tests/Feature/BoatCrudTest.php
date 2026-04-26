<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoatCrudTest extends TestCase
{
    use RefreshDatabase;

    private function boatPayload(int $portId): array
    {
        return [
            'port_id'         => $portId,
            'name'            => 'Teszt Jacht',
            'description'     => 'Szép és kényelmes hajó.',
            'price_per_night' => 150,
            'currency'        => 'EUR',
            'is_active'       => true,
            'type'            => 'Yacht',
            'year_built'      => 2010,
            'capacity'        => 6,
            'width'           => 4.5,
            'length'          => 12.0,
            'draft'           => 1.8,
        ];
    }

    // --- CREATE ---

    public function test_authenticated_user_can_create_a_boat(): void
    {
        $user = User::factory()->create();
        $port = Port::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/newBoat', $this->boatPayload($port->id));

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'Teszt Jacht'])
            ->assertJsonFragment(['user_id' => $user->id]);

        $this->assertDatabaseHas('boats', [
            'user_id' => $user->id,
            'name'    => 'Teszt Jacht',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_a_boat(): void
    {
        $port = Port::factory()->create();

        $this->postJson('/api/newBoat', $this->boatPayload($port->id))
            ->assertUnauthorized();

        $this->assertDatabaseMissing('boats', ['name' => 'Teszt Jacht']);
    }

    public function test_boat_creation_fails_without_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/newBoat', [
            'name' => 'Hiányos Hajó',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('boats', ['name' => 'Hiányos Hajó']);
    }

    // --- READ ---

    public function test_anyone_can_list_boats(): void
    {
        Boat::factory()->count(3)->create(['is_active' => true]);

        $this->getJson('/api/boats')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_anyone_can_view_a_single_boat(): void
    {
        $boat = Boat::factory()->create(['is_active' => true]);

        $this->getJson("/api/boats/{$boat->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $boat->id]);
    }

    // --- UPDATE ---

    public function test_owner_can_update_their_own_boat(): void
    {
        $owner = User::factory()->create();
        $boat  = Boat::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner)->putJson("/api/boats/{$boat->id}", [
            'name'            => 'Frissített Név',
            'price_per_night' => 200,
        ]);

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Frissített Név'])
            ->assertJsonFragment(['price_per_night' => 200]);

        $this->assertDatabaseHas('boats', [
            'id'              => $boat->id,
            'name'            => 'Frissített Név',
            'price_per_night' => 200,
        ]);
    }

    public function test_non_owner_cannot_update_another_users_boat(): void
    {
        $owner   = User::factory()->create();
        $stranger = User::factory()->create();
        $boat    = Boat::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($stranger)->putJson("/api/boats/{$boat->id}", [
            'name' => 'Lopott Szerkesztés',
        ]);

        $response->assertForbidden()
            ->assertJsonFragment(['message' => 'You are not allowed to edit this boat']);

        $this->assertDatabaseMissing('boats', ['name' => 'Lopott Szerkesztés']);
    }

    public function test_admin_can_update_any_boat(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create();
        $boat  = Boat::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($admin)->putJson("/api/boats/{$boat->id}", [
            'name' => 'Admin Szerkesztés',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Admin Szerkesztés']);
    }
}
