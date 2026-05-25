<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');

        $users = User::when($searchTerm, function ($query, $searchTerm) {
            return $query->where('first_name', 'like', "%{$searchTerm}%")->orWhere('last_name', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%");
        })
            ->paginate(10);

        return response()->json($users);
    }

    public function updateSystemRole(Request $request, User $user)
    {
        if (auth()->user()->system_role !== 'admin') {
            return response()->json(['message' => 'Aukaat se bahar!'], 403);
        }

        $request->validate([
            'system_role' => 'required|in:admin,employee,client'
        ]);

        $user->update(['system_role' => $request->system_role]);

        return response()->json(['message' => 'User role updated successfully']);
    }

    // Parameter se "User $user" hata diya
    public function updateProfile(Request $request)
    {
        // Auth user ko yahan fetch kar, ye 100% secure aur bulletproof hai
        $user = $request->user();

        $validatedData = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name'  => 'sometimes|required|string|max:255',
            'profile_pic'     => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->has('first_name')) {
            $user->first_name = $validatedData['first_name'];
        }

        if ($request->has('last_name')) {
            $user->last_name = $validatedData['last_name'];
        }

        if ($request->hasFile('profile_pic')) {
            if ($user->profile_pic) {
                Storage::disk('public')->delete($user->profile_pic);
            }
            $path = $request->file('profile_pic')->store('profile_pics', 'public');
            $user->profile_pic = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ], 200);
    }
}
