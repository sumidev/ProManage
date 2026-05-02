<?php

namespace App\Notifications;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProjectInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database','broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->invitation->project->id,
            'project_name' => $this->invitation->project->name,
            'inviter_name' => $this->invitation->inviter->first_name . ' ' . $this->invitation->inviter->last_name,
            'message' => 'invited you to join the project',
            'type' => 'project_invitation',
            'action_url' => 'invitations/accept?token='.$this->invitation->token,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message'      => 'invited you to collaborate on',
            'project_name' => $this->invitation->project->name,
            'inviter_name' => $this->invitation->inviter->name,
            'token'        => $this->invitation->token,
            'action_url'   => '/invitations/' . $this->invitation->token,
        ]);
    }
}
