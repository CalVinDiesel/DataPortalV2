<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "--- 🚀 REPLACING SFTPGO SYNC FUNCTION (v241) ---\n";
    
    $sftpRoot = rtrim(config('filesystems.disks.sftp_delivery.root', '/home/tiquan/uploads/'), '/');

    DB::unprepared("
        CREATE OR REPLACE FUNCTION sync_to_sftpgo()
        RETURNS TRIGGER AS $$
        BEGIN
            IF (NEW.role IN ('trusted', 'admin', 'superadmin') AND NEW.is_active = true) THEN
                DECLARE
                    v_role_label TEXT;
                BEGIN
                    v_role_label := CASE 
                        WHEN NEW.role IN ('admin', 'superadmin') THEN 'Role: Admin' 
                        ELSE 'Role: Client' 
                    END;
                    
                    INSERT INTO users (
                        username, password, status, expiration_date, description, 
                        home_dir, uid, gid, max_sessions, quota_size, quota_files, permissions,
                        used_quota_size, used_quota_files, last_quota_update, 
                        upload_bandwidth, download_bandwidth, last_login,
                        upload_data_transfer, download_data_transfer, total_data_transfer,
                        used_upload_data_transfer, used_download_data_transfer,
                        created_at, updated_at, last_password_change, 
                        first_upload, first_download, filesystem,
                        deleted_at, role_id
                    )
                    VALUES (
                        NEW.sftp_username, 
                        NEW.sftp_password, 
                        1, 0, 'Data Portal Sync: ' || NEW.name || ' | ' || v_role_label,
                        '{$sftpRoot}/' || NEW.sftp_username,
                        1000, 1000, 0, 0, 0, '{\"/\": [\"*\"]}',
                        0, 0, 0, 
                        0, 0, 0,
                        0, 0, 0,
                        0, 0,
                        (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint, 
                        (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint, 
                        0, 0, 0, 0,
                        0, NULL -- set role_id to NULL
                    )
                    ON CONFLICT (username) DO UPDATE SET
                        password = EXCLUDED.password,
                        home_dir = EXCLUDED.home_dir,
                        description = EXCLUDED.description,
                        status = 1,
                        updated_at = (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint;
                END;
            ELSE
                DELETE FROM users WHERE username = NEW.sftp_username;
            END IF;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
    ");
    
    echo "✅ Function updated successfully. Trigger should now use the new logic.\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
