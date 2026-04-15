<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ClientUploads', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('ClientUploads', 'google_drive_link')) {
                $table->text('google_drive_link')->nullable();
            }
            if (!Schema::hasColumn('ClientUploads', 'output_categories')) {
                $table->json('output_categories')->nullable();
            }
            if (!Schema::hasColumn('ClientUploads', 'organization_name')) {
                $table->string('organization_name')->nullable();
            }
            if (!Schema::hasColumn('ClientUploads', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ClientUploads', function (Blueprint $table) {
            $table->dropColumn(['google_drive_link', 'output_categories', 'organization_name', 'updated_at']);
        });
    }
};
