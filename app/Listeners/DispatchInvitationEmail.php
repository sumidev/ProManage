<?php

namespace App\Listeners;

use App\Events\InvitationCreated;
use App\Mail\ProjectInvitationMail;
use Illuminate\Support\Facades\Mail;

class DispatchInvitationEmail
{
    public function handle(InvitationCreated $event): void
    {
        $invitation = $event->invitation->loadMissing(['project', 'inviter']);

        Mail::to($invitation->email)->send(new ProjectInvitationMail($invitation));
    }
}
