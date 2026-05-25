<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // 🚀 PATH DIFFERENTIATION (v162): Manual IF EXISTS instead of ON CONFLICT
    DB::unprepared("
        CREATE OR REPLACE FUNCTION sync_to_sftpgo()
        RETURNS TRIGGER AS $$
        DECLARE
            v_home_dir TEXT;
        BEGIN
            IF (NEW.role IN ('admin', 'superadmin', 'trusted') AND NEW.is_active = true) THEN
                
                -- Construct Path
                IF (NEW.role IN ('admin', 'superadmin')) THEN
                    v_home_dir := '/home/tiquan/delivered/' || NEW.sftp_username;
                ELSE
                    v_home_dir := '/home/tiquan/uploads/' || NEW.sftp_username;
                END IF;

                -- Check for existing
                IF EXISTS (SELECT 1 FROM public.users WHERE username = NEW.sftp_username) THEN
                    UPDATE public.users SET 
                        home_dir = v_home_dir,
                        status = 1,
                        updated_at = (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint
                    WHERE username = NEW.sftp_username;
                ELSE
                    INSERT INTO public.users (
                        username, password, status, expiration_date, description, 
                        home_dir, uid, gid, max_sessions, quota_size, quota_files, permissions,
                        created_at, updated_at, filesystem,
                        used_quota_size, used_quota_files, last_quota_update,
                        upload_bandwidth, download_bandwidth, last_login,
                        upload_data_transfer, download_data_transfer, total_data_transfer,
                        used_upload_data_transfer, used_download_data_transfer,
                        last_password_change, first_upload, first_download, deleted_at
                    )
                    VALUES (
                        NEW.sftp_username, 
                        '', 
                        1, 0, 'Sync',
                        v_home_dir,
                        1000, 1000, 0, 0, 0, '{\"/\":[\"*\"]}',
                        (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint, 
                        (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint, 
                        '0',
                        0, 0, 0,
                        0, 0, 0,
                        0, 0, 0,
                        0, 0,
                        0, 0, 0, 0
                    );
                END IF;
            ELSE
                DELETE FROM public.users WHERE username = NEW.sftp_username;
            END IF;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
    ");
    echo "TRIGGER RE-CREATED SUCCESSFULLY (v162).\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
