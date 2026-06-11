<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Drop trigger if it exists on DataPortalUsers table
            DB::unprepared('DROP TRIGGER IF EXISTS trg_sync_sftpgo ON "DataPortalUsers"');
        } catch (\Exception $e) {
            // Ignore if table or trigger does not exist
        }

        try {
            // Drop trigger if it exists on portal_users table
            DB::unprepared('DROP TRIGGER IF EXISTS trg_sync_sftpgo ON portal_users');
        } catch (\Exception $e) {
            // Ignore if table or trigger does not exist
        }

        try {
            // Drop the trigger function
            DB::unprepared('DROP FUNCTION IF EXISTS sync_to_sftpgo()');
        } catch (\Exception $e) {
            // Ignore if function does not exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down action as we are transitioning completely to the API approach
    }
};
