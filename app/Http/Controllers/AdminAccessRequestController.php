<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccessRequest;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserInvitation;

class AdminAccessRequestController extends Controller
{
    public function index()
    {
        $requests = AccessRequest::where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->get();
        return response()->json($requests);
    }

    public function approve($id)
    {
        $accessRequest = AccessRequest::findOrFail($id);

        if ($accessRequest->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Request is not pending.'], 400);
        }

        // Generate invitation token
        $token = Str::random(60);
        $expiresAt = now()->addHours(48);

        // Map to user table
        $namePrefix = Str::replace(' ', '', $accessRequest->name);
        $sftpUsername = $namePrefix . '_' . Str::lower(Str::random(8));
        $sftpPassword = Str::random(12);
        
        $user = User::create([
            'name' => $accessRequest->name,
            'email' => $accessRequest->email,
            'username' => $namePrefix . '_' . Str::random(8), // Internal username
            'sftp_username' => $sftpUsername,
            'sftp_password' => $sftpPassword,
            'role' => 'pending', 
            'provider' => 'pending',
            'is_active' => false,
            'invitation_token' => $token,
            'invitation_expires_at' => $expiresAt,
        ]);

        // Update request status
        $accessRequest->status = 'approved';
        $accessRequest->save();

        // Send Email
        $setupUrl = url("/setup?token={$token}");
        Mail::to($user->email)->send(new UserInvitation($user->name, $setupUrl));

        return response()->json(['success' => true, 'message' => 'Request approved and invitation sent.']);
    }

    public function reject($id)
    {
        $accessRequest = AccessRequest::findOrFail($id);
        $accessRequest->status = 'rejected';
        $accessRequest->save();

        return response()->json(['success' => true, 'message' => 'Request rejected.']);
    }
}
