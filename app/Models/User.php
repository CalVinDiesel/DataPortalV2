<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted()
    {
        static::saved(function ($user) {
            if ($user->wasRecentlyCreated || $user->wasChanged(['role', 'is_active', 'sftp_username', 'sftp_password', 'email'])) {
                \App\Services\SFTPGoService::syncUser($user);
            }
        });

        static::deleted(function ($user) {
            // 1. Delete SFTPGo Account
            if ($user->sftp_username) {
                \App\Services\SFTPGoService::deleteUser($user->sftp_username);
            }

            // 2. Delete physical SFTP directories on the SSH host server
            if ($user->sftp_username) {
                try {
                    $sftpDisk = \Illuminate\Support\Facades\Storage::disk('sftp_delivery');
                    $userUploadsDir = 'uploads/' . $user->sftp_username;
                    $userDeliveredDir = 'delivered/' . $user->sftp_username;

                    if ($sftpDisk->exists($userUploadsDir)) {
                        $sftpDisk->deleteDirectory($userUploadsDir);
                    }
                    if ($sftpDisk->exists($userDeliveredDir)) {
                        $sftpDisk->deleteDirectory($userDeliveredDir);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("User deletion: Could not delete SFTP directories for {$user->sftp_username}: " . $e->getMessage());
                }
            }

            // 3. Delete Nitro chunk folders for each upload
            try {
                $uploads = \App\Models\ClientUpload::where('created_by_email', $user->email)->get();
                foreach ($uploads as $upload) {
                    if (\Illuminate\Support\Facades\Storage::disk('nitro')->exists($upload->project_id)) {
                        \Illuminate\Support\Facades\Storage::disk('nitro')->deleteDirectory($upload->project_id);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("User deletion: Exception cleaning up Nitro chunks: " . $e->getMessage());
            }

            // 4. Delete all database records associated with the user's email or ID
            try {
                \App\Models\ClientUpload::where('created_by_email', $user->email)->delete();
                \App\Models\TokenWallet::where('user_email', $user->email)->delete();
                \App\Models\TokenTransaction::where('user_email', $user->email)->delete();
                \App\Models\MapDataPurchase::where('user_email', $user->email)->delete();
                \App\Models\StripePayment::where('user_email', $user->email)->delete();
                \App\Models\AccessRequest::where('email', $user->email)->delete();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("User deletion: Exception cleaning database records: " . $e->getMessage());
            }
        });
    }

    protected $fillable = [
        'name', 'email', 'password', 'username', 'contact_number', 'role', 
        'provider', 'stripe_customer_id', 'is_active', 'invitation_token', 
        'invitation_expires_at', 'oauth_id', 'sftp_username', 'sftp_password',
        'viewable_password', 'previous_role', 'status', 'login_method', 'provider_id',
        'home_dir', 'sftp_quota_size'
    ];

    protected $hidden = [
        'password',
        'viewable_password',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_users';

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'invitation_expires_at' => 'datetime',
        ];
    }

    /**
     * 🚀 SECURE BUT VIEWABLE (v148): Custom Accessor for SFTP Password
     * This allows us to decrypt the password for the user while keeping it "scrambled" in the DB.
     * It also includes a "Failsafe" so old hashed passwords don't crash the site.
     */
    protected function sftpPassword(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                try {
                    $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($value);
                    // If it is a bcrypt or argon hash, trigger self-healing
                    if (\Illuminate\Support\Str::startsWith($decrypted, '$2y$') || \Illuminate\Support\Str::startsWith($decrypted, '$argon2')) {
                        return $this->regenerateAndSyncSftpPassword();
                    }
                    return $decrypted;
                } catch (\Exception $e) {
                    // Failsafe/Self-heal: If it is an old one-way hash or cannot be decrypted, regenerate it
                    return $this->regenerateAndSyncSftpPassword();
                }
            },
            set: fn ($value) => $value ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null,
        );
    }

    /**
     * Automatically regenerate the SFTP password for self-healing and sync it.
     */
    protected function regenerateAndSyncSftpPassword(): string
    {
        $plainText = self::generateSecureSftpPassword(12);
        $encrypted = \Illuminate\Support\Facades\Crypt::encryptString($plainText);
        
        // Update database directly to avoid Eloquent saved recursion
        \Illuminate\Support\Facades\DB::table('portal_users')
            ->where('id', $this->id)
            ->update(['sftp_password' => $encrypted]);

        // Keep local model instance attributes in sync
        $this->attributes['sftp_password'] = $encrypted;

        // Sync to SFTPGo
        \App\Services\SFTPGoService::syncUser($this, $plainText);

        return $plainText;
    }

    /**
     * 🚀 REVERSIBLE LOGIN PASSWORD (v155): Custom Accessor for Viewable Password
     * This allows users to view their own login password in the profile.
     */
    protected function viewablePassword(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                try {
                    return \Illuminate\Support\Facades\Crypt::decryptString($value);
                } catch (\Exception $e) {
                    return null;
                }
            },
            set: fn ($value) => $value ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null,
        );
    }

    /**
     * Accessor for is_active.
     */
    public function getIsActiveAttribute($value)
    {
        return $this->status === 'active';
    }

    /**
     * Mutator for is_active.
     */
    public function setIsActiveAttribute($value)
    {
        $isActive = (bool)$value;
        $this->attributes['is_active'] = $isActive;
        $this->attributes['status'] = $isActive ? 'active' : 'pending';
    }

    /**
     * Accessor for provider.
     */
    public function getProviderAttribute($value)
    {
        $method = $this->login_method;
        if ($method === 'password') {
            return 'local';
        }
        return $method ?: $value;
    }

    /**
     * Mutator for provider.
     */
    public function setProviderAttribute($value)
    {
        $this->attributes['provider'] = $value;
        if ($value === 'local') {
            $this->attributes['login_method'] = 'password';
        } else {
            $this->attributes['login_method'] = $value;
        }
    }

    /**
     * Accessor for oauth_id.
     */
    public function getOauthIdAttribute($value)
    {
        return $this->provider_id ?: $value;
    }

    /**
     * Mutator for oauth_id.
     */
    public function setOauthIdAttribute($value)
    {
        $this->attributes['oauth_id'] = $value;
        $this->attributes['provider_id'] = $value;
    }

    /**
     * Generate a secure, cryptographically random SFTP password
     * excluding ambiguous/look-alike characters (0, O, o, 1, I, l).
     */
    public static function generateSecureSftpPassword(int $length = 12): string
    {
        $pool = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $password = '';
        $max = strlen($pool) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $pool[random_int(0, $max)];
        }
        return $password;
    }

    /**
     * Get all active admin and superadmin emails dynamically.
     *
     * @return array
     */
    public static function getAdminEmails(): array
    {
        try {
            $emails = self::whereIn('role', ['superadmin', 'admin'])
                ->where('status', 'active')
                ->pluck('email')
                ->toArray();
        } catch (\Exception $e) {
            $emails = [];
        }

        // Fallbacks from environment configuration
        $superAdminEnv = env('SUPER_ADMIN_EMAIL');
        if ($superAdminEnv) {
            $emails[] = $superAdminEnv;
        }

        // Return unique, non-empty emails
        return array_values(array_unique(array_filter($emails)));
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
