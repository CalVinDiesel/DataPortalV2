<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    private function googleDriver()
    {
        return Socialite::driver('google')->setHttpClient(
            new \GuzzleHttp\Client(['verify' => 'C:/php85/cacert.pem'])
        );
    }

    public function redirectToGoogle()
    {
        return $this->googleDriver()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = $this->googleDriver()->user();
        } catch (\Exception $e) {
            \Log::error('Google OAuth failed', ['error' => $e->getMessage()]);
            return redirect('/login')->with('error', 'Google login failed: ' . $e->getMessage());
        }

        \Log::info('Google user retrieved', ['email' => $googleUser->getEmail()]);

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            \Log::warning('User not found', ['email' => $googleUser->getEmail()]);
            return redirect('/login')->withErrors([
                'email' => 'No account found for this Google email. Contact your administrator to get access.',
            ]);
        }

        \Log::info('User found in database', ['user_id' => $user->id, 'email' => $user->email]);

        if ($user->provider === 'local') {
            $user->update(['provider' => 'google']);
        }

        Auth::login($user);

        \Log::info('Auth::login called', [
            'user_id' => $user->id,
            'auth_check' => Auth::check(),
            'auth_user' => Auth::user() ? Auth::user()->email : 'null'
        ]);

        request()->session()->regenerate();

        \Log::info('Session regenerated', ['session_id' => session()->getId()]);

        return redirect('/');
    }
}
