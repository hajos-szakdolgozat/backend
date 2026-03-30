<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Notifications\Reservations\ReservationStatusUpdatedNotification;
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
    public function reservationsByMe(Request $request, $id)
    {
        if ((int) $request->user()->id !== (int) $id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $reservations = Reservation::with(['boat.boatImages', 'review', 'user'])
            ->whereHas('boat', function ($query) use ($id) {
                $query->where('user_id', $id);
            })
            ->get();

        return response()->json($reservations, 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $reservation = Reservation::with(['boat.user', 'user'])->find($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reservation not found',
            ], 404);
        }

        if (!$reservation->boat || (int) $reservation->boat->user_id !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($reservation->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending reservations can be updated',
            ], 422);
        }

        $reservation->status = $validated['status'];
        $reservation->save();

        if ($reservation->user) {
            $reservation->user->notify(new ReservationStatusUpdatedNotification($reservation));
        }

        return response()->json([
            'message' => 'Reservation status updated successfully',
            'reservation' => $reservation->fresh(['boat.boatImages', 'review', 'user']),
        ], 200);
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
