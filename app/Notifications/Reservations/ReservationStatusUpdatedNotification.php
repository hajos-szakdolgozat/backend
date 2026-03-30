<?php

namespace App\Notifications\Reservations;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Reservation $reservation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation;
        $status = strtolower((string) $reservation->status);
        $isApproved = $status === 'approved';
        $appName = config('app.name', 'DockJet');
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
        $reservationsUrl = $frontendUrl.'/reservations';
        $boatName = $reservation->boat?->name ?? 'a kiválasztott hajó';
        $ownerName = $reservation->boat?->user?->name ?? 'a hirdető';

        return (new MailMessage)
            ->subject($isApproved ? 'Foglalás jóváhagyva' : 'Foglalás elutasítva')
            ->markdown('emails.reservations.status-updated', [
                'name' => $notifiable->name ?? 'Felhasználó',
                'appName' => $appName,
                'reservationsUrl' => $reservationsUrl,
                'boatName' => $boatName,
                'ownerName' => $ownerName,
                'status' => $status,
                'isApproved' => $isApproved,
                'startDate' => $this->formatDate($reservation->start_date),
                'endDate' => $this->formatDate($reservation->end_date),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $reservation = $this->reservation;
        $isApproved = strtolower((string) $reservation->status) === 'approved';
        $reservationsUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/').'/reservations';
        $boatName = $reservation->boat?->name ?? 'Ismeretlen hajó';

        return [
            'title' => $isApproved ? 'Foglalás jóváhagyva' : 'Foglalás elutasítva',
            'message' => sprintf(
                'A(z) %s hajóra leadott foglalásod státusza %s lett (%s - %s).',
                $boatName,
                $isApproved ? 'jóváhagyva' : 'elutasítva',
                $this->formatDate($reservation->start_date),
                $this->formatDate($reservation->end_date),
            ),
            'action_url' => $reservationsUrl,
            'action_label' => 'Foglalásaim',
            'meta' => [
                'reservation_id' => $reservation->id,
                'boat_id' => $reservation->boat_id,
                'boat_name' => $boatName,
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