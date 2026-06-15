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
            if ($user->wasRecentlyCreated || $user->wasChanged(['role', 'is_active', 'sftp_username', 'sftp_password'])) {
                \App\Services\SFTPGoService::syncUser($user);
            }
        });

        static::deleted(function ($user) {
            if ($user->sftp_username) {
                \App\Services\SFTPGoService::deleteUser($user->sftp_username);
            }
        });
    }

    protected $fillable = [
        'name', 'email', 'password', 'username', 'contact_number', 'role', 
        'provider', 'stripe_customer_id', 'is_active', 'invitation_token', 
        'invitation_expires_at', 'oauth_id', 'sftp_username', 'sftp_password',
        'viewable_password'
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
}
