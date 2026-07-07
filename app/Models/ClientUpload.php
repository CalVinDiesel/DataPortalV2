<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientUpload extends Model
{
    protected $table = 'client_uploads';

    protected $fillable = [
        'project_id',
        'project_title',
        'project_description',
        'upload_type',
        'file_count',
        'file_paths',
        'camera_models',
        'capture_date',
        'created_by_email',
        'request_status',
        'rejected_reason',
        'decided_at',
        'decided_by',
        'drone_pos_file_path',
        'google_drive_link',
        'latitude',
        'longitude',
        'category',
        'output_categories',
        'image_metadata',
        'tokens_charged',
        'total_size_bytes',
        'delivery_method',
        'sftp_delivery_path',
        'gdrive_delivery_folder_id',
        'delivered_file_path',
        'delivered_at',
        'delivered_expires_at',
        'onedrive_link',
        'onedrive_item_id',
        'onedrive_drive_id',
        'cloud_provider'
    ];

    protected $casts = [
        'file_paths' => 'array',
        'output_categories' => 'array',
        'decided_at' => 'datetime',
        'capture_date' => 'date',
        'delivered_at' => 'datetime',
        'delivered_expires_at' => 'datetime',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s.uP');
    }

    /**
     * Calculate total storage size in bytes used by the user (excluding hidden).
     */
    public static function calculateUserStorageUsed($email)
    {
        return (int) self::where('created_by_email', $email)
            ->where('request_status', '!=', 'user_hidden')
            ->sum('total_size_bytes');
    }

    public static function getStorageLimitBytes($email = null)
    {
        $defaultPortalLimitGb = (float) env('DATA_PORTAL_STORAGE_LIMIT_GB', 10);
        $defaultPortalLimitBytes = (int) ($defaultPortalLimitGb * 1024 * 1024 * 1024);

        if ($email) {
            $user = \App\Models\User::where('email', $email)->first();
            if ($user && !is_null($user->sftp_quota_size)) {
                if ($user->sftp_quota_size <= 0) {
                    return 9999 * 1024 * 1024 * 1024; // 9999 GB (effectively unlimited)
                }
                
                $defaultSftpLimitGb = (float) env('SFTPGO_STORAGE_LIMIT_GB', 5);
                $defaultSftpLimitBytes = (int) ($defaultSftpLimitGb * 1024 * 1024 * 1024);
                
                if ($user->sftp_quota_size !== $defaultSftpLimitBytes) {
                    $ratio = $defaultSftpLimitBytes > 0 ? ($defaultPortalLimitBytes / $defaultSftpLimitBytes) : 2.0;
                    return (int) ($user->sftp_quota_size * $ratio);
                }
            }
        }
        
        return $defaultPortalLimitBytes;
    }

    /**
     * Check if the user has exceeded their storage limit, optionally with an additional size.
     */
    public static function hasExceededStorageLimit($email, $additionalBytes = 0)
    {
        $used = self::calculateUserStorageUsed($email);
        return ($used + $additionalBytes) > self::getStorageLimitBytes($email);
    }
}

