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
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();
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
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        $user->is_active = false;
        $user->save();
        
        return response()->json(['success' => true, 'message' => 'User removed from data portal.']);
    }
}
