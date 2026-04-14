<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    private function getDriver($provider)
    {
        return Socialite::driver($provider)->setHttpClient(
            new \GuzzleHttp\Client(['verify' => 'C:/php85/cacert.pem'])
        );
    }

    public function redirectToProvider($provider)
    {
        return $this->getDriver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = $this->getDriver($provider)->user();
        } catch (\Exception $e) {
            \Log::error("{$provider} OAuth failed", ['error' => $e->getMessage()]);
            return redirect('/login')->with('error', ucfirst($provider) . ' login failed: ' . $e->getMessage());
        }

        // Check if user is currently going through the Initial Setup Flow
        if (session()->has('setup_token')) {
            $token = session('setup_token');
            $contact = session('setup_contact_number');
            
            $user = User::where('invitation_token', $token)->first();

            if ($user && $user->invitation_expires_at >= now() && $user->email === $socialUser->getEmail()) {
                // Perfect Match! Commit the Setup Data
                $user->provider = $provider;
                $user->oauth_id = $socialUser->getId();
                $user->contact_number = $contact;
                $user->role = 'registered';
                $user->is_active = true;
                $user->invitation_token = null;
                $user->invitation_expires_at = null;
                $user->save();

                Auth::login($user);
                session()->forget(['setup_token', 'setup_contact_number']);
                return redirect()->route('landing')->with('success', 'Setup Complete!');
            } else {
                session()->forget(['setup_token', 'setup_contact_number']);
                return redirect('/login')->withErrors(['email' => 'Setup link expired or invalid email match for OAuth.']);
            }
        }

        // STANDARD LOGIN
        $user = User::where('email', $socialUser->getEmail())->first();
        $superAdminEmail = env('SUPER_ADMIN_EMAIL');

        // SELF-HEALING & IMMORTALITY FOR SUPER ADMIN
        if ($socialUser->getEmail() === $superAdminEmail) {
            if (!$user) {
                // Recreate Missing Immortal Account
                $user = User::create([
                    'name' => $socialUser->getName() ?? env('SUPER_ADMIN_NAME', 'Super Admin'),
                    'email' => $socialUser->getEmail(),
                    'username' => env('SUPER_ADMIN_USER', 'superadmin'),
                    'password' => \Illuminate\Support\Facades\Hash::make(Str::random(32)), // Random as it uses OAuth
                    'role' => 'superadmin',
                    'is_active' => true,
                    'provider' => $provider,
                    'oauth_id' => $socialUser->getId(),
                    'sftp_username' => Str::replace(' ', '', $socialUser->getName() ?? 'Admin') . '_' . Str::lower(Str::random(8)),
                    'sftp_password' => Str::random(12),
                ]);
            } else {
                // Restore & Update Provider (Persistence)
                $user->role = 'superadmin';
                $user->is_active = true;
                $user->provider = $provider; // Persistence: Set to current login method
                $user->oauth_id = $socialUser->getId();
                
                // Ensure SFTP exists
                if (empty($user->sftp_username)) {
                    $user->sftp_username = Str::replace(' ', '', $user->name) . '_' . Str::lower(Str::random(8));
                }
                if (empty($user->sftp_password)) {
                    $user->sftp_password = Str::random(12);
                }
                
                $user->save();
            }
        }

        if (! $user) {
            return redirect('/login')->withErrors([
                'email' => 'No account found. Request access from an administrator.',
            ]);
        }

        if (!$user->is_active) {
            return redirect('/login')->withErrors([
                'email' => 'Your account is pending. Please check your email for the setup link.',
            ]);
        }

        if ($user->provider === 'local') {
            return redirect('/login')->withErrors([
                'email' => 'You registered this account with an Email/Password setup. Please sign in normally.',
            ]);
        }

        if ($user->provider !== $provider) {
            return redirect('/login')->withErrors([
                'email' => 'You registered this account with ' . ucfirst($user->provider) . '. Please use that service to sign in.',
            ]);
        }

        // Sync oauth ID if it somehow got lost but provider matches
        if (empty($user->oauth_id)) {
            $user->oauth_id = $socialUser->getId();
            $user->save();
        }

        Auth::login($user);
        request()->session()->regenerate();
        return redirect('/');
    }

    public function redirectToGoogle() { return $this->redirectToProvider('google'); }
    public function handleGoogleCallback() { return $this->handleProviderCallback('google'); }

    public function redirectToMicrosoft() { return $this->redirectToProvider('microsoft'); }
    public function handleMicrosoftCallback() { return $this->handleProviderCallback('microsoft'); }
}
