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
        'invitation_expires_at', 'oauth_id', 'sftp_username', 'sftp_password'
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'DataPortalUsers';

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
}
