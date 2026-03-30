<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
        $resetUrl = $frontendUrl.'/reset-password?token='.$this->token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
        $expireMinutes = (int) config(
            'auth.passwords.'.config('auth.defaults.passwords', 'users').'.expire',
            60,
        );

        return (new MailMessage)
            ->subject('DockJet - Jelszó visszaállítása')
            ->markdown('emails.auth.reset-password', [
                'name' => $notifiable->name ?? 'Felhasználó',
                'resetUrl' => $resetUrl,
                'expireMinutes' => $expireMinutes,
                'appName' => config('app.name', 'DockJet'),
            ]);
    }
}
