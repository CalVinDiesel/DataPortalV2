<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseQuotation extends Model
{
    protected $fillable = [
        'purchase_id',
        'user_id',
        'user_email',
        'output_categories',
        'area_coordinates',
        'status',
    ];

    protected $casts = [
        'output_categories' => 'array',
        'area_coordinates' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
