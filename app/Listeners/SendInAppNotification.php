<?php

namespace App\Listeners;

use App\Events\InvitationCreated;
use App\Models\User;
use App\Notifications\ProjectInviteNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendInAppNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InvitationCreated $event): void
    {
        $invitation = $event->invitation;

        // User check karo ki existing hai ya nahi
        $user = User::where('email', $invitation->email)->first();

        // Agar account hai, toh Database Notification fire kar do
        if ($user) {
            Log::info('User exists. Sending DB notification for user ID: ' . $user->id);
            $user->notify(new ProjectInviteNotification($invitation));
        }
        Log::info('User does not exist. Skipping DB notification.');
    }
}
