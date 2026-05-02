<?php

namespace App\Jobs;

use App\Mail\ProjectInvitationMail;
use App\Models\ProjectInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvitationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invitation;
    /**
     * Create a new job instance.
     */
    public function __construct(ProjectInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->invitation->email)->send(new ProjectInvitationMail($this->invitation));
    }
}
