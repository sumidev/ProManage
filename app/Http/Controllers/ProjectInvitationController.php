<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvitation;
use Illuminate\Http\Request;

class ProjectInvitationController extends Controller
{
    public function show($token)
    {
        $invitation = ProjectInvitation::with(['project', 'inviter'])
            ->where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json(['success' => false, 'message' => 'Invitation link is invalid or expired.'], 404);
        }

        return response()->json(['success' => true, 'data' => $invitation]);
    }

    public function respond(Request $request, $token)
    {
        $request->validate([
            'action' => 'required|in:accept,reject'
        ]);

        $invitation = ProjectInvitation::where('token', $token)->where('status', 'pending')->first();

        if (!$invitation) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired invitation.'], 404);
        }

        if ($request->user()->email !== $invitation->email) {
            return response()->json(['success' => false, 'message' => 'This invitation is not for your email address.'], 403);
        }

        if ($request->action === 'accept') {
            $invitation->project->members()->attach($request->user()->id, ['role' => 'member']);
            $invitation->update(['status' => 'accepted']);
            $message = 'You have successfully joined the project.';
        } else {
            $invitation->update(['status' => 'rejected']);
            $message = 'You have declined the invitation.';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }
}
