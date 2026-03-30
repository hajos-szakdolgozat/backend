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
        return ['mail'];
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

    private function formatDate(?string $date): string
    {
        if (!$date) {
            return '-';
        }

        return Carbon::parse($date)->locale('hu')->translatedFormat('Y. F j.');
    }
}