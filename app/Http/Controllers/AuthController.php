<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'name' => $user->name,
            'email' => $user->email,
            'contactNumber' => $user->contact_number,
            'hasPassword' => !empty($user->password),
            'role' => $user->role ?? 'registered',
            'provider' => $user->provider ?? 'local',
            'account_removed' => !empty($user->removed_at),
            'removal_reason' => $user->removal_reason,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['loggedIn' => false]);
        }

        return response()->json([
            'loggedIn' => true,
            'account_removed' => !empty($user->removed_at),
            'removal_reason' => $user->removal_reason,
            'message' => !empty($user->removed_at) ? 'Your account has been removed.' : null
        ]);
    }

    public function updateName(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $user = $request->user();
        $user->name = $request->name;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Name updated.', 'name' => $user->name]);
    }

    public function updateContact(Request $request)
    {
        $request->validate(['contactNumber' => 'nullable|string|max:64']);
        $user = $request->user();
        $user->contact_number = $request->contactNumber;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Contact number updated.']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'currentPassword' => 'required|current_password',
            'newPassword' => ['required', Password::defaults()],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->newPassword);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password updated.']);
    }

    public function sftp(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'sftpUsername' => $user->username ?? 'sftp_' . strtolower(explode('@', $user->email)[0]),
            'sftpPassword' => '*********'
        ]);
    }

    public function updateSftpPassword(Request $request)
    {
        $request->validate(['newPassword' => 'required|string|min:8']);
        // Store SFTP password logic here - for now just returning success
        return response()->json(['success' => true, 'message' => 'SFTP password updated.']);
    }
}
