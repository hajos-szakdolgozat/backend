<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\Reservations\ReservationCreatedNotification;
use App\Notifications\Reservations\ReservationStatusUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_reservation_for_another_users_boat(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $guest = User::factory()->create();
        $boat = Boat::factory()->create([
            'user_id' => $owner->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($guest)->postJson('/api/reservations', [
            'boat_id' => $boat->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertCreated()
            ->assertJsonFragment([
                'boat_id' => $boat->id,
                'user_id' => $guest->id,
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('reservations', [
            'boat_id' => $boat->id,
            'user_id' => $guest->id,
            'status' => 'pending',
        ]);

        Notification::assertSentTo($owner, ReservationCreatedNotification::class);
    }

    public function test_user_cannot_reserve_their_own_boat(): void
    {
        $owner = User::factory()->create();
        $boat = Boat::factory()->create([
            'user_id' => $owner->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->postJson('/api/reservations', [
            'boat_id' => $boat->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'A saját hajódat nem foglalhatod le.',
            ]);

        $this->assertDatabaseMissing('reservations', [
            'boat_id' => $boat->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_user_cannot_create_a_reservation_for_an_inactive_boat(): void
    {
        $owner = User::factory()->create();
        $guest = User::factory()->create();
        $boat = Boat::factory()->create([
            'user_id' => $owner->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($guest)->postJson('/api/reservations', [
            'boat_id' => $boat->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Ez a hirdetés inaktív, ezért nem foglalható.',
            ]);

        $this->assertDatabaseMissing('reservations', [
            'boat_id' => $boat->id,
            'user_id' => $guest->id,
        ]);
    }

    public function test_boat_owner_can_approve_a_pending_reservation(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $guest = User::factory()->create();
        $boat = Boat::factory()->create([
            'user_id' => $owner->id,
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'user_id' => $guest->id,
            'boat_id' => $boat->id,
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(9)->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($owner)->patchJson("/api/reservations/{$reservation->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Reservation status updated successfully',
            ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'approved',
        ]);

        Notification::assertSentTo($guest, ReservationStatusUpdatedNotification::class);
    }
}
