<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Reservation;
use App\Notifications\Reservations\ReservationCreatedNotification;
use App\Notifications\Reservations\ReservationStatusUpdatedNotification;
use Carbon\Carbon;
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
        $reservation = Reservation::with(['boat.boatImages', 'boat.boatAmenities.amenity', 'review'])
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

        if ($validated['status'] === 'approved') {
            $hasConflict = $this->hasApprovedOverlap(
                $reservation->boat_id,
                $reservation->start_date,
                $reservation->end_date,
                $reservation->id,
            );

            if ($hasConflict) {
                return response()->json([
                    'message' => 'This reservation overlaps with another approved reservation.',
                ], 422);
            }
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
            'boat_id' => 'required|exists:boats,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        $boat = Boat::with('user')->find($validated['boat_id']);

        if (!$boat) {
            return response()->json([
                'message' => 'Boat not found',
            ], 404);
        }

        if (!$boat->is_active) {
            return response()->json([
                'message' => 'Ez a hirdetés inaktív, ezért nem foglalható.',
            ], 422);
        }

        if ((int) $boat->user_id === (int) $request->user()->id) {
            return response()->json([
                'message' => 'A saját hajódat nem foglalhatod le.',
            ], 422);
        }

        if ($this->hasApprovedOverlap($boat->id, $validated['start_date'], $validated['end_date'])) {
            return response()->json([
                'message' => 'Erre az időpontra már van jóváhagyott foglalás.',
            ], 422);
        }

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'boat_id' => $validated['boat_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'] ?? 'pending',
        ]);

        $reservation->load(['boat.user', 'user']);

        if ($boat->user && (int) $boat->user->id !== (int) $request->user()->id) {
            $boat->user->notify(new ReservationCreatedNotification($reservation));
        }

        return response()->json($reservation, 201);
    }

    private function hasApprovedOverlap(int $boatId, string $startDate, string $endDate, ?int $ignoreReservationId = null): bool
    {
        return Reservation::query()
            ->where('boat_id', $boatId)
            ->where('status', 'approved')
            ->when($ignoreReservationId, function ($query) use ($ignoreReservationId) {
                $query->where('id', '!=', $ignoreReservationId);
            })
            ->whereDate('start_date', '<', Carbon::parse($endDate)->toDateString())
            ->whereDate('end_date', '>', Carbon::parse($startDate)->toDateString())
            ->exists();
    }
}
