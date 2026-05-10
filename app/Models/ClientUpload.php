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
}
