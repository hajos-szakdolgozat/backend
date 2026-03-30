<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'DockJet');
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');

        return (new MailMessage)
            ->subject('Üdv a DockJeten!')
            ->markdown('emails.auth.welcome', [
                'name' => $notifiable->name ?? 'Felhasználó',
                'appName' => $appName,
                'homeUrl' => $frontendUrl,
            ]);
    }
}
