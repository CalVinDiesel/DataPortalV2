<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the Trigger Function with the EXACT column list found in your DB
        DB::unprepared("
            CREATE OR REPLACE FUNCTION sync_to_sftpgo()
            RETURNS TRIGGER AS $$
            BEGIN
                IF (NEW.role IN ('trusted', 'admin') AND NEW.is_active = true) THEN
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
                        1, 0, 'Data Portal Sync: ' || NEW.name,
                        '/home/tiquan/uploads/' || NEW.sftp_username,
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
                        status = 1,
                        updated_at = (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint;
                ELSE
                    DELETE FROM users WHERE username = NEW.sftp_username;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 2. Attach the trigger
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_sync_sftpgo ON \"DataPortalUsers\";
            CREATE TRIGGER trg_sync_sftpgo
            AFTER INSERT OR UPDATE ON \"DataPortalUsers\"
            FOR EACH ROW
            EXECUTE FUNCTION sync_to_sftpgo();
        ");
        
        // 3. Initial Sync
        DB::unprepared("
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
            SELECT 
                sftp_username, sftp_password, 1, 0, 'Initial Sync: ' || name,
                '/home/tiquan/uploads/' || sftp_username,
                2000, 2000, 0, 0, 0, '{\"/\": [\"*\"]}',
                0, 0, 0, 
                0, 0, 0,
                0, 0, 0,
                0, 0,
                (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint, 
                (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint, 
                0, 0, 0, 0,
                0, NULL
            FROM \"DataPortalUsers\"
            WHERE role IN ('trusted', 'admin') AND is_active = true
            ON CONFLICT (username) DO NOTHING;
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_sync_sftpgo ON "DataPortalUsers"');
        DB::unprepared('DROP FUNCTION IF EXISTS sync_to_sftpgo()');
    }
};
