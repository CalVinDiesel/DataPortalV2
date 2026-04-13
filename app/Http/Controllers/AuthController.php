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
            'sftpUsername' => $user->sftp_username ?? 'Not set',
            'sftpPassword' => $user->sftp_password ?? ''
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
            'role' => $user->role ?? 'registered',
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
            'sftpUsername' => $user->sftp_username ?? 'Not set',
            'sftpPassword' => $user->sftp_password ?? ''
        ]);
    }

    public function updateSftpPassword(Request $request)
    {
        $request->validate(['newPassword' => 'required|string|min:8']);
        
        $user = $request->user();
        $user->sftp_password = $request->newPassword;
        $user->save();

        return response()->json(['success' => true, 'message' => 'SFTP password updated.']);
    }
}
