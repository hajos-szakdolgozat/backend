<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['boat.boatImages', 'review'])->get();

        return response()->json($reservations, 200);
    }

    public function myReservations(Request $request)
    {
        $reservations = Reservation::with(['boat.boatImages', 'review'])
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($reservations, 200);
    }

    public function myReservation(Request $request, $id)
    {
        $reservation = Reservation::with(['boat.boatImages', 'review'])
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'message' => 'Reservation not found'
            ], 404);
        }

        return response()->json($reservation, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'boat_id' => 'required|exists:boats,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        $reservation = Reservation::create([
            'user_id' => $validated['user_id'],
            'boat_id' => $validated['boat_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'] ?? 'pending',
        ]);

        return response()->json($reservation, 201);
    }
}
