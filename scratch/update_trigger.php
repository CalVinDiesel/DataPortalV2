<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sql = "
CREATE OR REPLACE FUNCTION sync_to_sftpgo()
RETURNS TRIGGER AS $$
DECLARE
    v_home_dir TEXT;
BEGIN
    IF (NEW.role IN ('trusted', 'admin', 'superadmin', 'registered') AND NEW.is_active = true) THEN
        -- 🚀 ROLE-BASED PATHING (v167): user is upload/, admin is delivers/
        IF (NEW.role IN ('admin', 'superadmin')) THEN
            v_home_dir := '/home/tiquan/delivers/' || NEW.sftp_username;
        ELSE
            v_home_dir := '/home/tiquan/upload/' || NEW.sftp_username;
        END IF;

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
                created_at, updated_at, filesystem
            )
            VALUES (
                NEW.sftp_username, 
                '', 
                1, 0, 'Data Portal Sync',
                v_home_dir,
                1000, 1000, 0, 0, 0, '{\"/\": [\"*\"]}',
                (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint, 
                (EXTRACT(EPOCH FROM NOW()) * 1000)::bigint, 
                0
            );
        END IF;
    ELSE
        DELETE FROM public.users WHERE username = NEW.sftp_username;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
";

DB::unprepared($sql);
echo "Trigger updated successfully.\n";

// Also update existing users in public.users to reflect new home_dir
DB::statement("
    UPDATE public.users u
    SET home_dir = CASE 
        WHEN du.role IN ('admin', 'superadmin') THEN '/home/tiquan/delivers/' || du.sftp_username
        ELSE '/home/tiquan/upload/' || du.sftp_username
    END
    FROM public.\"DataPortalUsers\" du
    WHERE u.username = du.sftp_username;
");
echo "Existing SFTP users updated.\n";
