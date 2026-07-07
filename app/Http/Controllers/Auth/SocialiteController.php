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
        $options = [];
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && file_exists('C:/php85/cacert.pem')) {
            $options['verify'] = 'C:/php85/cacert.pem';
        }

        return Socialite::driver($provider)->setHttpClient(
            new \GuzzleHttp\Client($options)
        );
    }

    public function redirectToProvider($provider)
    {
        return $this->getDriver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        \Log::info("SocialiteController::handleProviderCallback: callback received for provider '{$provider}'");
        \Log::info("SocialiteController::handleProviderCallback: session relink_user_id = " . (session()->has('relink_user_id') ? session('relink_user_id') : 'NOT_SET'));
        try {
            $socialUser = $this->getDriver($provider)->user();
        } catch (\Exception $e) {
            \Log::error("{$provider} OAuth failed", ['error' => $e->getMessage()]);
            if (session()->has('relink_user_id')) {
                session()->forget('relink_user_id');
                return redirect('/profile')->with('error', 'OAuth authentication failed: ' . $e->getMessage());
            }
            return redirect('/login')->with('error', ucfirst($provider) . ' login failed: ' . $e->getMessage());
        }

        // RELINKING FLOW (Approach 1: active OAuth verification to change email)
        if (session()->has('relink_user_id')) {
            $userId = session('relink_user_id');
            session()->forget('relink_user_id');

            $user = User::find($userId);
            if (!$user) {
                return redirect('/profile')->with('error', 'User account not found.');
            }

            $newEmail = $socialUser->getEmail();
            if (empty($newEmail)) {
                return redirect('/profile')->with('error', "Could not retrieve email address from your {$provider} account.");
            }

            // Check if another user already has this email
            $exists = User::where('email', $newEmail)->where('id', '!=', $user->id)->exists();
            if ($exists) {
                return redirect('/profile')->with('error', "The {$provider} email ({$newEmail}) is already linked to another user account.");
            }

            // Check if another user already has this provider and oauth_id/provider_id
            $idExists = User::where('provider', $provider)
                ->where('id', '!=', $user->id)
                ->where(function ($q) use ($socialUser) {
                    $q->where('oauth_id', $socialUser->getId())
                      ->orWhere('provider_id', $socialUser->getId());
                })
                ->exists();
            if ($idExists) {
                return redirect('/profile')->with('error', "This {$provider} account is already linked to another user.");
            }

            // Update user credentials
            $user->email = $newEmail;
            $user->provider = $provider;
            $user->login_method = $provider;
            $user->oauth_id = $socialUser->getId();
            $user->provider_id = $socialUser->getId();
            $user->save();

            // Re-login as the updated user details
            Auth::login($user);

            try {
                \App\Services\SFTPGoService::syncUser($user);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to sync from SFTPGo on social login: " . $e->getMessage());
            }

            return redirect('/profile')->with('success', "Your account email has been successfully updated to {$newEmail} and linked to your {$provider} account.");
        }

        // Check if user is currently going through the Initial Setup Flow
        if (session()->has('setup_token')) {
            $token = session('setup_token');
            
            $user = User::where('invitation_token', $token)->first();

            if ($user && $user->invitation_expires_at >= now() && $user->email === $socialUser->getEmail()) {
                // Perfect Match! Commit the Setup Data
                $user->login_method = $provider;
                $user->provider_id = $socialUser->getId();
                $user->provider = $provider;
                $user->oauth_id = $socialUser->getId();
                $user->role = 'registered';
                $user->status = 'active';
                $user->is_active = true;
                $user->invitation_token = null;
                $user->invitation_expires_at = null;
                $user->save();

                Auth::login($user);
                
                try {
                    \App\Services\SFTPGoService::syncUser($user);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to sync from SFTPGo on social setup: " . $e->getMessage());
                }

                session()->forget(['setup_token']);
                return redirect()->route('landing')->with('success', 'Setup Complete!');
            } else {
                session()->forget(['setup_token']);
                return redirect('/login')->withErrors(['email' => 'Setup link expired or invalid email match for OAuth.']);
            }
        }

        // STANDARD LOGIN: Match by OAuth provider ID first (handles email change support)
        $user = null;
        if (!empty($socialUser->getId())) {
            $user = User::where('provider', $provider)
                ->where(function ($q) use ($socialUser) {
                    $q->where('oauth_id', $socialUser->getId())
                      ->orWhere('provider_id', $socialUser->getId());
                })
                ->first();
        }

        // Fallback to email matching
        if (!$user) {
            $user = User::where('email', $socialUser->getEmail())->first();
        }

        $superAdminEmail = env('SUPER_ADMIN_EMAIL');

        // SELF-HEALING & IMMORTALITY FOR SUPER ADMIN
        if ($socialUser->getEmail() === $superAdminEmail) {
            if (!$user) {
                // Recreate Missing Immortal Account
                $user = User::create([
                    'name' => $socialUser->getName() ?? env('SUPER_ADMIN_NAME', 'Super Admin'),
                    'email' => $socialUser->getEmail(),
                    'username' => env('SUPER_ADMIN_USER', 'superadmin'),
                    'password' => \Illuminate\Support\Facades\Hash::make($randPass = Str::random(32)),
                    'viewable_password' => $randPass,
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
                if (empty($user->provider)) {
                    $user->provider = $provider; // Persistence: Set to current login method
                }
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
                'email' => 'No account found. Please contact an administrator.',
            ]);
        }

        if ($user->status === 'pending') {
            return redirect('/login')->withErrors([
                'email' => 'Your account is pending activation. Please check your email inbox for the activation link.',
            ]);
        }

        if ($user->login_method === 'password') {
            return redirect('/login')->withErrors([
                'email' => 'You registered this account with an Email/Password setup. Please sign in normally.',
            ]);
        }

        if ($user->login_method && $user->login_method !== $provider) {
            $methodName = $user->login_method === 'password' ? 'Password' : ucfirst($user->login_method);
            return redirect('/login')->withErrors([
                'email' => 'You registered this account with ' . $methodName . '. Please use that service to sign in.',
            ]);
        }

        // Sync oauth ID if it somehow got lost but provider matches
        if (empty($user->oauth_id)) {
            $user->oauth_id = $socialUser->getId();
            $user->save();
        }

        Auth::login($user);
        
        try {
            \App\Services\SFTPGoService::syncUser($user);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to sync from SFTPGo on social login: " . $e->getMessage());
        }

        request()->session()->regenerate();

        if ($user->role === 'admin' || $user->role === 'superadmin') {
            return redirect()->route('admin_dashboard');
        }

        return redirect('/');
    }

    public function redirectToGoogle() { return $this->redirectToProvider('google'); }
    public function handleGoogleCallback() { return $this->handleProviderCallback('google'); }

    public function redirectToMicrosoft() { return $this->redirectToProvider('microsoft'); }
    public function handleMicrosoftCallback() { return $this->handleProviderCallback('microsoft'); }

    public function redirectToRelink($provider)
    {
        if (!in_array($provider, ['google', 'microsoft'])) {
            abort(404);
        }
        $currentUserId = auth()->id();
        \Log::info("SocialiteController::redirectToRelink: setting relink_user_id = {$currentUserId} for provider '{$provider}'");
        session(['relink_user_id' => $currentUserId]);
        session()->save(); // Force save session file immediately
        \Log::info("SocialiteController::redirectToRelink: session saved successfully. Redirecting user to provider...");
        return $this->getDriver($provider)->redirect();
    }
}
