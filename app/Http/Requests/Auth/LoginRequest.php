<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $superAdminEmail = env('SUPER_ADMIN_EMAIL');
        $superAdminPassword = env('SUPER_ADMIN_PASSWORD');

        if ($this->email === $superAdminEmail && Hash::check($this->password, Hash::make($superAdminPassword))) {
            $user = \App\Models\User::where('email', $this->email)->first();

            if (!$user) {
                // Self-Heal: Recreate the immortal account
                $name = env('SUPER_ADMIN_NAME', 'Super Admin');
                $user = \App\Models\User::create([
                    'name' => $name,
                    'email' => $this->email,
                    'username' => env('SUPER_ADMIN_USER', 'superadmin'),
                    'password' => Hash::make($this->password),
                    'role' => 'superadmin',
                    'is_active' => true,
                    'provider' => 'local',
                    'sftp_username' => Str::replace(' ', '', $name) . '_' . Str::lower(Str::random(8)),
                    'sftp_password' => Str::random(12),
                ]);
            } else {
                // Self-Heal: Restore role and activity
                $user->role = 'superadmin';
                $user->is_active = true;
                $user->provider = 'local';
                
                // Ensure SFTP exists
                if (empty($user->sftp_username)) {
                    $user->sftp_username = Str::replace(' ', '', $user->name) . '_' . Str::lower(Str::random(8));
                }
                if (empty($user->sftp_password)) {
                    $user->sftp_password = Str::random(12);
                }

                if (env('SUPER_ADMIN_FORCE_PASSWORD', false)) {
                    $user->password = Hash::make($this->password);
                }
                $user->save();
            }
        }

        $user = \App\Models\User::where('email', $this->email)->first();

        if ($user) {
            if (!$user->is_active) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'Your account is pending setup. Please check your email inbox for the original setup link.',
                ]);
            }
            if ($user->provider !== 'local') {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'You registered this account with ' . ucfirst($user->provider) . '. Please click "Sign in with ' . ucfirst($user->provider) . '" to access your account.',
                ]);
            }
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
