<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Jobs\SendResetPasswordEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        $token = Str::random(64);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Sync dispatch so email sends without a queue worker in local dev
        SendResetPasswordEmail::dispatchSync($email, $token);

        return response()->json([
            'message' => 'If an account exists for that email, we sent a password reset link.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Record check karo database se
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        // Check if token exists and is valid (e.g., within 1 hour)
        if (
            !$record
            || !Hash::check($request->token, $record->token)
            || Carbon::parse($record->created_at)->addHour()->isPast()
        ) {
            return response()->json(['message' => 'Invalid or expired reset link. Please request a new one.'], 400);
        }

        // Password update
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Token clean up
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Your password has been reset. You can log in now.'], 200);
    }
}