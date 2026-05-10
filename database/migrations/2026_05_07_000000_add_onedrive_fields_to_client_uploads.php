<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 🚀 ONEDRIVE INTEGRATION (v265): Adding specific columns to support Microsoft OneDrive 
     * alongside the existing Google Drive implementation.
     */
    public function up(): void
    {
        Schema::table('client_uploads', function (Blueprint $table) {
            // We use specific naming to distinguish from google_drive_link
            $table->text('onedrive_link')->nullable()->after('google_drive_link');
            $table->string('onedrive_item_id')->nullable()->after('onedrive_link');
            $table->string('onedrive_drive_id')->nullable()->after('onedrive_item_id');
            
            // Add a generic provider column to make future logic cleaner
            // Defaulting to 'sftp' or 'google_drive' based on existing data would be complex, 
            // so we leave it nullable for now.
            $table->string('cloud_provider')->nullable()->after('upload_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_uploads', function (Blueprint $table) {
            $table->dropColumn(['onedrive_link', 'onedrive_item_id', 'onedrive_drive_id', 'cloud_provider']);
        });
    }
};
