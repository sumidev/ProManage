<?php

namespace App\Listeners;

use App\Events\InvitationCreated;
use App\Jobs\SendInvitationEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DispatchInvitationEmail implements ShouldQueue
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
        SendInvitationEmail::dispatch($event->invitation);
    }
}
