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
        $this->ensureSftpCredentials($user);
        $hasSftpAccess = in_array($user->role, ['trusted', 'admin', 'superadmin']);

        if ($hasSftpAccess && $user->sftp_username) {
            try {
                \App\Services\SFTPGoService::syncUser($user);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to sync user on profile view: " . $e->getMessage());
            }
        }

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
            'sftpUsername' => $hasSftpAccess ? ($user->sftp_username ?? 'Not set') : 'Not set',
            'sftpPassword' => $hasSftpAccess ? ($user->sftp_password ?? '') : '',
            'viewablePassword' => $user->viewable_password ?? '',
            'sftpHost' => config('filesystems.disks.sftp_delivery.host') ?: $request->getHost(),
            'sftpPort' => env('CLIENT_SFTP_PORT', env('SFTP_PORT', 2222)),
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
        
        $contactNumber = $request->contactNumber;
        if ($contactNumber) {
            $cleanNumber = preg_replace('/[^0-9]/', '', $contactNumber);
            if (!empty($cleanNumber)) {
                $user = $request->user();
                $exists = \App\Models\User::whereRaw("regexp_replace(contact_number, '[^0-9]', '', 'g') = ?", [$cleanNumber])
                    ->where('id', '!=', $user->id)
                    ->exists();
                
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This contact number has already been used by other users in this data portal.'
                    ], 422);
                }
            }
        }

        $user = $request->user();
        $user->contact_number = $contactNumber;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Contact number updated.']);
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'newEmail' => 'required|email|max:255|unique:portal_users,email,' . $request->user()->id,
        ]);

        $user = $request->user();
        $user->email = $request->newEmail;
        $user->save();

        // Invalidate session and logout to secure identity change
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Email updated. Please log in with your new email.',
            'requireRelogin' => true
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        if ($user->provider && $user->provider !== 'local') {
            return response()->json([
                'success' => false,
                'message' => 'Password management is not available for social login accounts.'
            ], 400);
        }

        $request->validate([
            'currentPassword' => 'required|current_password',
            'newPassword' => ['required', Password::defaults()],
        ]);

        $user->password = Hash::make($request->newPassword);
        $user->viewable_password = $request->newPassword; // Store viewable version
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password updated.']);
    }

    public function sftp(Request $request)
    {
        $user = $request->user();
        $this->ensureSftpCredentials($user);
        $hasSftpAccess = in_array($user->role, ['trusted', 'admin', 'superadmin']);

        return response()->json([
            'success' => true,
            'sftpUsername' => $hasSftpAccess ? ($user->sftp_username ?? 'Not set') : 'Not set',
            'sftpPassword' => $hasSftpAccess ? ($user->sftp_password ?? '') : '',
            'sftpHost' => config('filesystems.disks.sftp_delivery.host') ?: $request->getHost(),
            'sftpPort' => env('CLIENT_SFTP_PORT', env('SFTP_PORT', 2222)),
        ]);
    }

    public function updateSftpPassword(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['trusted', 'admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'SFTP access is not allowed for your role.'], 403);
        }

        $request->validate(['newPassword' => 'required|string|min:8']);
        
        $user->sftp_password = $request->newPassword;
        $user->save();

        return response()->json(['success' => true, 'message' => 'SFTP password updated.']);
    }

    protected function ensureSftpCredentials($user)
    {
        $hasSftpAccess = in_array($user->role, ['trusted', 'admin', 'superadmin']) && $user->is_active;
        if ($hasSftpAccess && empty($user->sftp_username)) {
            try {
                $rawPassword = \App\Models\User::generateSecureSftpPassword(12);
                $user->sftp_username = \Illuminate\Support\Str::slug($user->name) . '_' . strtolower(\Illuminate\Support\Str::random(6));
                $user->sftp_password = $rawPassword;
                $user->save();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to auto-generate SFTP credentials: " . $e->getMessage());
            }
        }
    }
}
