<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseQuotation extends Model
{
    protected $fillable = [
        'purchase_id',
        'user_id',
        'user_email',
        'map_data_id',
        'output_categories',
        'area_coordinates',
        'status',
        'admin_notes',
        'rejection_reason',
        'quoted_price',
        'quoted_at',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'payment_deadline',
        'processing_started_at',
    ];

    protected $casts = [
        'output_categories'     => 'array',
        'area_coordinates'      => 'array',
        'quoted_price'          => 'float',
        'quoted_at'             => 'datetime',
        'payment_deadline'      => 'date',
        'processing_started_at' => 'datetime',
    ];

    /**
     * Valid status transitions in order.
     */
    public const STATUSES = [
        'pending',
        'reviewed',
        'quoted',
        'awaiting_payment',
        'processing',
        'completed',
        'rejected',
    ];

    /**
     * Human-readable status labels.
     */
    public const STATUS_LABELS = [
        'pending'          => 'Pending Review',
        'reviewed'         => 'Under Review',
        'quoted'           => 'Quotation Sent',
        'awaiting_payment' => 'Awaiting Payment',
        'processing'       => 'Processing',
        'completed'        => 'Completed',
        'rejected'         => 'Rejected',
    ];

    /**
     * Bootstrap color class per status (for badges).
     */
    public const STATUS_COLORS = [
        'pending'          => 'warning',
        'reviewed'         => 'info',
        'quoted'           => 'primary',
        'awaiting_payment' => 'warning',
        'processing'       => 'purple',
        'completed'        => 'success',
        'rejected'         => 'danger',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mapData()
    {
        return $this->belongsTo(MapData::class, 'map_data_id', 'mapDataID');
    }

    /**
     * Scope to filter quotations for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get a human-readable label for the current status.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get the Bootstrap color class for the current status.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }
}
