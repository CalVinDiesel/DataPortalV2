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
                    return \Illuminate\Support\Facades\Crypt::decryptString($value);
                } catch (\Exception $e) {
                    // Failsafe: If it's an old one-way hash, return it as-is so the UI doesn't crash
                    return $value; 
                }
            },
            set: fn ($value) => $value ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null,
        );
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
}
