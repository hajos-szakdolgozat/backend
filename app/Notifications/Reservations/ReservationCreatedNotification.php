<?php

namespace App\Notifications\Reservations;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReservationCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Reservation $reservation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $reservation = $this->reservation;
        $boatName = $reservation->boat?->name ?? 'Ismeretlen hajó';
        $guestName = $reservation->user?->name ?? 'Ismeretlen felhasználó';
        $reservationUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/').'/myReservations';

        return [
            'title' => 'Új foglalás érkezett',
            'message' => sprintf(
                '%s foglalást adott le a(z) %s hajóra %s és %s között.',
                $guestName,
                $boatName,
                $this->formatDate($reservation->start_date),
                $this->formatDate($reservation->end_date),
            ),
            'action_url' => $reservationUrl,
            'action_label' => 'Beérkezett foglalások',
            'meta' => [
                'reservation_id' => $reservation->id,
                'boat_id' => $reservation->boat_id,
                'boat_name' => $boatName,
                'guest_name' => $guestName,
                'status' => $reservation->status,
            ],
        ];
    }

    private function formatDate(?string $date): string
    {
        if (!$date) {
            return '-';
        }

        return Carbon::parse($date)->locale('hu')->translatedFormat('Y. F j.');
    }
}