<?php

namespace App\Listeners;

use App\Events\InvitationCreated;
use App\Models\User;
use App\Notifications\ProjectInviteNotification;

class SendInAppNotification
{
    public function handle(InvitationCreated $event): void
    {
        $invitation = $event->invitation->loadMissing(['project', 'inviter']);

        $user = User::where('email', $invitation->email)->first();

        if ($user) {
            $user->notify(new ProjectInviteNotification($invitation));
        }
    }
}
