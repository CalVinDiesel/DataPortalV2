<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'username', 'role', 'is_active', 'status')->orderBy('id', 'asc')->get();
        
        $mapped = $users->map(function ($u) {
            $data = $u->toArray();
            // A user is removed only if they are not active AND not pending setup
            $data['removedAt'] = ($u->status !== 'active' && $u->status !== 'pending') ? now()->toIso8601String() : null;
            return $data;
        });

        return response()->json($mapped);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|string|min:8|max:20',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'An account with this email already exists.']);
        }

        // Generate activation token
        $token = \Illuminate\Support\Str::random(60);
        $expiresAt = now()->addHours(48);

        $namePrefix = \Illuminate\Support\Str::replace(' ', '', $request->name);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $namePrefix . '_' . \Illuminate\Support\Str::random(8),
            'contact_number' => $request->contact_number,
            'role' => 'registered', // Default role
            'status' => 'pending',
            'login_method' => null,
            'provider' => 'pending', // compatibility mapping
            'is_active' => false,   // compatibility mapping
            'invitation_token' => $token,
            'invitation_expires_at' => $expiresAt,
            'sftp_username' => null,
            'sftp_password' => null,
        ]);

        // Send Email
        $activateUrl = url("/activate?token={$token}");
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\UserInvitation($user->name, $activateUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Send activation mail failed: ' . $e->getMessage());
            return response()->json(['success' => true, 'message' => 'User registered in database, but activation email failed: ' . $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'User registered and activation link sent successfully.']);
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

        $user->previous_role = $user->role;
        $user->role = 'admin';

        // Auto-generate SFTP credentials if they do not exist
        if (empty($user->sftp_username)) {
            $rawPassword = \App\Models\User::generateSecureSftpPassword(12);
            $user->sftp_username = \Illuminate\Support\Str::slug($user->name) . '_' . strtolower(\Illuminate\Support\Str::random(6));
            $user->sftp_password = $rawPassword;
        }

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
        
        // Dynamic SFTP generation if not already set
        if (empty($user->sftp_username)) {
            $rawPassword = \App\Models\User::generateSecureSftpPassword(12);
            $user->sftp_username = \Illuminate\Support\Str::slug($user->name) . '_' . strtolower(\Illuminate\Support\Str::random(6));
            $user->sftp_password = $rawPassword;
            $user->save();
        } else {
            $user->save();
        }

        // AUTO-CREATE PHYSICAL SFTP HOME DIRECTORY
        try {
            $sftpDisk = \Illuminate\Support\Facades\Storage::disk('sftp_delivery');
            $userBaseDir = 'uploads/' . $user->sftp_username;
            if (!$sftpDisk->exists($userBaseDir)) {
                $sftpDisk->makeDirectory($userBaseDir);
                $sftpDisk->setVisibility($userBaseDir, 'public');
            }
            
            // Force 777 permissions using SSH if possible
            try {
                $sshPort = (int)config('filesystems.disks.sftp_delivery.port', 22);
                $ssh = new \phpseclib3\Net\SSH2(config('filesystems.disks.sftp_delivery.host'), $sshPort);
                if ($ssh->login(config('filesystems.disks.sftp_delivery.username'), config('filesystems.disks.sftp_delivery.password'))) {
                    $baseUploadRoot = rtrim(config('filesystems.disks.sftp_delivery.root', '/'), '/');
                    $userDir = $baseUploadRoot . '/uploads/' . $user->sftp_username;
                    $ssh->exec("chmod -R 777 " . escapeshellarg($userDir));
                }
            } catch (\Exception $sshEx) {
                \Illuminate\Support\Facades\Log::error("SSH Chmod failed on upgradeTrusted: " . $sshEx->getMessage());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Could not auto-create SFTP directory on upgrade: ' . $e->getMessage());
        }

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

        if ($user && $user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot remove yourself from the data portal.'], 400);
        }

        // Prevent modifying the base Super Admin defined in .env or anyone with superadmin role
        if ($request->email === env('SUPER_ADMIN_EMAIL') || ($user && $user->role === 'superadmin')) {
            return response()->json(['success' => false, 'message' => 'The Super Admin cannot be removed.'], 403);
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        // Permanently delete the user (triggers User model's static::deleted hook for comprehensive cleanup)
        $user->delete();
        
        return response()->json(['success' => true, 'message' => 'User and all associated data permanently removed from the data portal.']);
    }

    public function resendInvitation(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        if ($user->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'User is not in pending status.']);
        }

        // Regenerate activation token and expiry
        $token = \Illuminate\Support\Str::random(60);
        $user->invitation_token = $token;
        $user->invitation_expires_at = now()->addHours(48);
        $user->save();

        // Send Email
        $activateUrl = url("/activate?token={$token}");
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\UserInvitation($user->name, $activateUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Resend activation mail failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Activation regenerated, but failed to send email: ' . $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Activation email resent successfully.']);
    }

    public function downgradeAdmin(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('superadmin');

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

        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'User is not an admin.']);
        }

        // Determine the role to restore
        $previousRole = $user->previous_role ?: 'registered';

        $user->role = $previousRole;
        $user->previous_role = null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Admin successfully downgraded to ' . $previousRole . '.']);
    }
}
