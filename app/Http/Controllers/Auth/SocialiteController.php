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
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google login failed.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Update provider if not set
            if ($user->provider === 'local') {
                $user->update(['provider' => 'google']);
            }
        } else {
            // Create a new user
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'username' => 'user_' . Str::random(8),
                'password' => Hash::make(Str::random(24)),
                'provider' => 'google',
                'role' => 'registered',
            ]);
        }

        Auth::login($user);

        return redirect()->intended(route('landing', absolute: false));
    }
}
