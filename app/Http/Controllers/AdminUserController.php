<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'username', 'role', 'is_active')->orderBy('id', 'asc')->get();
        
        $mapped = $users->map(function ($u) {
            $data = $u->toArray();
            // A user is removed only if they are not active AND not pending setup
            $data['removedAt'] = (!$u->is_active && $u->role !== 'pending') ? now()->toIso8601String() : null;
            return $data;
        });

        return response()->json($mapped);
    }

    public function promote(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('superadmin');

        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        // Prevent modifying the base Super Admin defined in .env or anyone with superadmin role
        if ($request->email === env('SUPER_ADMIN_EMAIL') || ($user && $user->role === 'superadmin')) {
            return response()->json(['success' => false, 'message' => 'The Super Admin role cannot be modified.'], 403);
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        $user->role = 'admin';
        $user->save();

        return response()->json(['success' => true, 'message' => 'User promoted to admin.']);
    }

    public function upgradeTrusted(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        // Prevent modifying the base Super Admin defined in .env or anyone with superadmin role
        if ($request->email === env('SUPER_ADMIN_EMAIL') || ($user && $user->role === 'superadmin')) {
            return response()->json(['success' => false, 'message' => 'The Super Admin role cannot be modified.'], 403);
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        $user->role = 'trusted';
        $user->save();

        return response()->json(['success' => true, 'message' => 'User upgraded to Trusted.']);
    }

    public function downgradeRegistered(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        // Prevent modifying the base Super Admin defined in .env or anyone with superadmin role
        if ($request->email === env('SUPER_ADMIN_EMAIL') || ($user && $user->role === 'superadmin')) {
            return response()->json(['success' => false, 'message' => 'The Super Admin cannot be downgraded.'], 403);
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        $user->role = 'registered';
        $user->save();

        return response()->json(['success' => true, 'message' => 'User downgraded to registered.']);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reason' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        // Prevent modifying the base Super Admin defined in .env or anyone with superadmin role
        if ($request->email === env('SUPER_ADMIN_EMAIL') || ($user && $user->role === 'superadmin')) {
            return response()->json(['success' => false, 'message' => 'The Super Admin cannot be removed.'], 403);
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        $user->is_active = false;
        $user->save();
        
        return response()->json(['success' => true, 'message' => 'User removed from data portal.']);
    }
}
