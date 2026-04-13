<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Showcase extends Model
{
    protected $table = 'Showcase';
    public $timestamps = false;
    
    protected $fillable = [
        'map_data_id',
        'display_order',
        'created_at',
    ];
}
