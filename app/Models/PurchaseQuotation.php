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
        'delivery_ready',
        'delivered_at',
    ];

    protected $casts = [
        'output_categories'     => 'array',
        'area_coordinates'      => 'array',
        'quoted_price'          => 'float',
        'quoted_at'             => 'datetime',
        'payment_deadline'      => 'date',
        'processing_started_at' => 'datetime',
        'delivery_ready'        => 'boolean',
        'delivered_at'          => 'datetime',
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
     * Custom Accessor for admin_notes: ensures it always returns an array/dictionary mapping status -> note text.
     */
    public function getAdminNotesAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        // Fallback: return as array mapping the current status to the string
        return [
            $this->status => $value
        ];
    }

    /**
     * Custom Mutator for admin_notes: serializes array to JSON before saving to the DB.
     */
    public function setAdminNotesAttribute($value)
    {
        $this->attributes['admin_notes'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Get the note for the current status.
     */
    public function getCurrentAdminNoteAttribute(): ?string
    {
        $notes = $this->admin_notes;
        return $notes[$this->status] ?? null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SFTP DELIVERY PATH HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Returns the relative SFTP path (relative to the sftp_delivery disk root)
     * where admin should upload 3D model tiles for this purchase order.
     * Path: purchase_deliveries/<purchase_id>
     */
    public function getSftpDeliveryRelativePath(): string
    {
        return 'purchase_deliveries/' . $this->purchase_id;
    }

    /**
     * Returns the absolute filesystem path on the SFTP server
     * where admin should upload 3D model tiles via WinSCP.
     * Derived dynamically from SYSTEM_SSH_STORAGE_ROOT env variable.
     */
    public function getSftpDeliveryAbsolutePath(): string
    {
        $root = rtrim(config('filesystems.disks.sftp_delivery.root', '/home/dataportal/sftpgo/sftpgo_home/data'), '/');
        return $root . '/' . $this->getSftpDeliveryRelativePath();
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
