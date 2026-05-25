<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public ?string $userName;

    public function __construct(
        public string $token,
        public string $email,
    ) {
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/');
        $this->resetUrl = $frontendUrl . '/reset-password?token=' . urlencode($this->token)
            . '&email=' . urlencode($this->email);

        $user = User::where('email', $this->email)->first();
        $this->userName = $user?->first_name;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your ProManage password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.reset-password',
            with: [
                'resetUrl' => $this->resetUrl,
                'userName' => $this->userName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
