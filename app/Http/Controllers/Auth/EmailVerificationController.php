<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification hash.');
        }

        if (! URL::hasValidSignature($request)) {
            abort(403, 'Verification link expired or invalid.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/');

        return redirect()->away($frontendUrl . '/login?verified=1');
    }
}
