<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\BoatImage;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BoatDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_another_users_boat_without_upcoming_reservations(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $owner = User::factory()->create();
        $boat = Boat::factory()->create([
            'user_id' => $owner->id,
        ]);

        Storage::disk('public')->put('boats/test-image.jpg', 'fake-image-content');

        BoatImage::create([
            'boat_id' => $boat->id,
            'path' => 'boats/test-image.jpg',
            'is_thumbnail' => true,
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/boats/{$boat->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Boat deleted successfully',
            ]);

        $this->assertDatabaseMissing('boats', [
            'id' => $boat->id,
        ]);

        $this->assertDatabaseMissing('boat_images', [
            'boat_id' => $boat->id,
        ]);

        $this->assertFalse(Storage::disk('public')->exists('boats/test-image.jpg'));
    }

    public function test_admin_can_delete_boat_with_upcoming_reservations(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $owner = User::factory()->create();
        $guest = User::factory()->create();
        $boat = Boat::factory()->create([
            'user_id' => $owner->id,
        ]);

        Reservation::create([
            'user_id' => $guest->id,
            'boat_id' => $boat->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/boats/{$boat->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Boat deleted successfully',
            ]);

        $this->assertDatabaseMissing('boats', [
            'id' => $boat->id,
        ]);
    }

    public function test_owner_cannot_delete_boat_with_upcoming_reservations(): void
    {
        $owner = User::factory()->create();
        $guest = User::factory()->create();
        $boat = Boat::factory()->create([
            'user_id' => $owner->id,
        ]);

        Reservation::create([
            'user_id' => $guest->id,
            'boat_id' => $boat->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($owner)->deleteJson("/api/boats/{$boat->id}");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'This boat cannot be deleted because it has active or upcoming reservations.',
            ]);

        $this->assertDatabaseHas('boats', [
            'id' => $boat->id,
        ]);
    }
}