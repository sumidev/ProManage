<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->unreadNotifications;

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->unreadNotifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }
}
