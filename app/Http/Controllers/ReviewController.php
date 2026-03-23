<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $reservation = Reservation::with('review')->find($validated['reservation_id']);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found.'], 404);
        }

        if ((int) $reservation->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'You can only review your own reservation.'], 403);
        }

        if ($reservation->review) {
            return response()->json(['message' => 'This reservation has already been reviewed.'], 422);
        }

        $alreadyReviewedBoat = Review::whereHas('reservation', function ($query) use ($request, $reservation) {
            $query->where('user_id', $request->user()->id)
                ->where('boat_id', $reservation->boat_id);
        })->exists();

        if ($alreadyReviewedBoat) {
            return response()->json(['message' => 'You can only write one review per boat.'], 422);
        }

        try {
            $review = Review::create([
                'reservation_id' => $reservation->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            return response()->json([
                'message' => 'Review created successfully.',
                'review' => $review->load('reservation.user')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function boatReviews($boatId)
    {
        $reviews = Review::whereHas('reservation', function ($query) use ($boatId) {
            $query->where('boat_id', $boatId);
        })->with(['reservation.user'])->get();

        return response()->json($reviews);
    }

    public function userReviews($userId)
    {
        $reviews = Review::whereHas('reservation', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['reservation.boat'])->get();

        return response()->json($reviews);
    }
}
