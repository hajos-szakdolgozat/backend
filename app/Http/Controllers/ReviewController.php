<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $reservation = Reservation::find($request->reservation_id);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found.'], 404);
        }

        try {
            $review = Review::create([
                'reservation_id' => $reservation->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            return response()->json([
                'message' => 'Review created successfully.',
                'review' => $review
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
