<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
}
