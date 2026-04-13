<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessingRequest extends Model
{
    protected $table = 'ProcessingRequests';
    public $timestamps = false;
    
    protected $fillable = [
        'upload_id',
        'status',
        'requested_at',
        'requested_by',
        'completed_at',
        'result_tileset_url',
        'notes',
        'delivered_at',
        'delivery_notes'
    ];
}
