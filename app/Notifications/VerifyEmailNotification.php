<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    use Queueable;

    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        $verifyUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your ProManage account')
            ->view('emails.auth.verify-email', [
                'userName' => trim($notifiable->first_name . ' ' . $notifiable->last_name),
                'verifyUrl' => $verifyUrl,
            ]);
    }
}
