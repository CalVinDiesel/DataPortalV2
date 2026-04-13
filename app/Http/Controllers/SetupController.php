<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    public function index(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect('/')->withErrors(['token' => 'Missing setup token.']);
        }

        $user = User::where('invitation_token', $token)->first();

        if (!$user || $user->invitation_expires_at < now()) {
            return redirect('/')->withErrors(['email' => 'Your setup link is invalid or has expired. Please contact an administrator to request a new one.']);
        }

        return view('portal.setup', compact('token', 'user'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'contact_number' => 'required|string|min:8|max:20',
            'action' => 'required|in:password,google,microsoft',
        ]);

        $token = $request->input('token');
        $user = User::where('invitation_token', $token)->first();

        if (!$user || $user->invitation_expires_at < now()) {
            return redirect('/login')->withErrors(['email' => 'Your setup link is invalid or has expired.']);
        }

        $action = $request->input('action');

        if ($action === 'password') {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Complete Setup
            $user->contact_number = $request->input('contact_number');
            $user->password = Hash::make($request->input('password'));
            $user->provider = 'local';
            $user->role = 'registered';
            $user->is_active = true;
            $user->invitation_token = null;
            $user->invitation_expires_at = null;
            $user->save();

            Auth::login($user);
            return redirect()->route('landing')->with('success', 'Your account setup is complete.');
        }

        // OAuth Handling Branch B
        // Save token and contact number in session then redirect to Socialite
        session([
            'setup_token' => $token,
            'setup_contact_number' => $request->input('contact_number')
        ]);

        if ($action === 'google') {
            return redirect('/auth/google'); // the socialite route
        } elseif ($action === 'microsoft') {
            return redirect('/auth/microsoft');
        }
    }
}
