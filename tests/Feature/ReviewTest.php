<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function makeCompletedReservation(User $guest, Boat $boat): Reservation
    {
        return Reservation::create([
            'user_id'    => $guest->id,
            'boat_id'    => $boat->id,
            'status'     => 'approved',
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date'   => now()->subDays(5)->toDateString(),
        ]);
    }

    public function test_user_can_leave_a_review_for_their_reservation(): void
    {
        $owner       = User::factory()->create();
        $guest       = User::factory()->create();
        $boat        = Boat::factory()->create(['user_id' => $owner->id, 'is_active' => true]);
        $reservation = $this->makeCompletedReservation($guest, $boat);

        $response = $this->actingAs($guest)->postJson('/api/reviews', [
            'reservation_id' => $reservation->id,
            'rating'         => 4,
            'comment'        => 'Nagyszerű volt!',
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Review created successfully.'])
            ->assertJsonFragment(['rating' => 4]);

        $this->assertDatabaseHas('reviews', [
            'reservation_id' => $reservation->id,
            'rating'         => 4,
        ]);
    }

    public function test_user_cannot_review_someone_elses_reservation(): void
    {
        $owner       = User::factory()->create();
        $guest       = User::factory()->create();
        $stranger    = User::factory()->create();
        $boat        = Boat::factory()->create(['user_id' => $owner->id, 'is_active' => true]);
        $reservation = $this->makeCompletedReservation($guest, $boat);

        $response = $this->actingAs($stranger)->postJson('/api/reviews', [
            'reservation_id' => $reservation->id,
            'rating'         => 5,
            'comment'        => 'Próba szöveg',
        ]);

        $response->assertForbidden()
            ->assertJsonFragment(['message' => 'You can only review your own reservation.']);

        $this->assertDatabaseMissing('reviews', ['reservation_id' => $reservation->id]);
    }

    public function test_user_cannot_review_the_same_reservation_twice(): void
    {
        $owner       = User::factory()->create();
        $guest       = User::factory()->create();
        $boat        = Boat::factory()->create(['user_id' => $owner->id, 'is_active' => true]);
        $reservation = $this->makeCompletedReservation($guest, $boat);

        Review::create([
            'reservation_id' => $reservation->id,
            'rating'         => 3,
            'comment'        => 'Első értékelés',
        ]);

        $response = $this->actingAs($guest)->postJson('/api/reviews', [
            'reservation_id' => $reservation->id,
            'rating'         => 5,
            'comment'        => 'Második kísérlet',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'This reservation has already been reviewed.']);

        $this->assertSame(1, Review::where('reservation_id', $reservation->id)->count());
    }

    public function test_user_cannot_review_the_same_boat_twice_on_different_reservations(): void
    {
        $owner        = User::factory()->create();
        $guest        = User::factory()->create();
        $boat         = Boat::factory()->create(['user_id' => $owner->id, 'is_active' => true]);
        $reservation1 = $this->makeCompletedReservation($guest, $boat);
        $reservation2 = Reservation::create([
            'user_id'    => $guest->id,
            'boat_id'    => $boat->id,
            'status'     => 'approved',
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date'   => now()->subDays(20)->toDateString(),
        ]);

        Review::create([
            'reservation_id' => $reservation1->id,
            'rating'         => 4,
            'comment'        => 'Első értékelés',
        ]);

        $response = $this->actingAs($guest)->postJson('/api/reviews', [
            'reservation_id' => $reservation2->id,
            'rating'         => 5,
            'comment'        => 'Próbálok újra értékelni',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'You can only write one review per boat.']);
    }

    public function test_review_requires_rating_between_1_and_5(): void
    {
        $owner       = User::factory()->create();
        $guest       = User::factory()->create();
        $boat        = Boat::factory()->create(['user_id' => $owner->id, 'is_active' => true]);
        $reservation = $this->makeCompletedReservation($guest, $boat);

        $response = $this->actingAs($guest)->postJson('/api/reviews', [
            'reservation_id' => $reservation->id,
            'rating'         => 10,
            'comment'        => 'Túl magas',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('reviews', ['reservation_id' => $reservation->id]);
    }

    public function test_unauthenticated_user_cannot_post_a_review(): void
    {
        $owner       = User::factory()->create();
        $guest       = User::factory()->create();
        $boat        = Boat::factory()->create(['user_id' => $owner->id]);
        $reservation = $this->makeCompletedReservation($guest, $boat);

        $response = $this->postJson('/api/reviews', [
            'reservation_id' => $reservation->id,
            'rating'         => 5,
            'comment'        => 'Nem bejelentkezett',
        ]);

        $response->assertUnauthorized();
    }
}
