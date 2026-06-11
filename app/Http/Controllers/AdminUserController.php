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
        
        // Dynamic SFTP generation if not already set
        if (empty($user->sftp_username)) {
            $rawPassword = \Illuminate\Support\Str::random(12);
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
