<?php

namespace App\Mail;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public ProjectInvitation $invitation;

    public string $acceptUrl;

    public string $inviterName;

    public string $projectName;

    public string $projectInitial;

    public ?string $projectDescription;

    public function __construct(ProjectInvitation $invitation)
    {
        $this->invitation = $invitation->loadMissing(['project', 'inviter']);

        $this->acceptUrl = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/')
            . '/invitations/accept?token=' . urlencode($this->invitation->token);

        $inviter = $this->invitation->inviter;
        $this->inviterName = trim(($inviter?->first_name ?? '') . ' ' . ($inviter?->last_name ?? '')) ?: 'A team member';

        $project = $this->invitation->project;
        $this->projectName = $project?->name ?? 'Untitled project';
        $this->projectInitial = Str::upper(Str::substr($this->projectName, 0, 1));
        $this->projectDescription = $project?->description;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re invited to join "' . $this->projectName . '" on ProManage',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.projects.invitation',
            with: [
                'acceptUrl' => $this->acceptUrl,
                'inviterName' => $this->inviterName,
                'projectName' => $this->projectName,
                'projectInitial' => $this->projectInitial,
                'projectDescription' => $this->projectDescription,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
