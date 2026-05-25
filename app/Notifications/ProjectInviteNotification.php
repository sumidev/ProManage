<?php

namespace App\Notifications;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProjectInviteNotification extends Notification
{
    use Queueable;

    public ProjectInvitation $invitation;

    public function __construct(ProjectInvitation $invitation)
    {
        $this->invitation = $invitation->loadMissing(['project', 'inviter']);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $inviter = $this->invitation->inviter;
        $inviterName = trim(($inviter?->first_name ?? '') . ' ' . ($inviter?->last_name ?? '')) ?: 'A team member';

        return [
            'project_id' => $this->invitation->project_id,
            'project_name' => $this->invitation->project?->name ?? 'Project',
            'inviter_name' => $inviterName,
            'message' => 'invited you to join the project',
            'type' => 'project_invitation',
            'token' => $this->invitation->token,
            'action_url' => '/invitations/accept?token=' . $this->invitation->token,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
