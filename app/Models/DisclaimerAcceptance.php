<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisclaimerAcceptance extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];
}
