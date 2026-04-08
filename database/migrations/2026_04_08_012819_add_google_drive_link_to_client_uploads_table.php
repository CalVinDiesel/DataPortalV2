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
            $table->string('google_drive_link', 2048)->nullable()->after('drone_pos_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ClientUploads', function (Blueprint $table) {
            $table->dropColumn('google_drive_link');
        });
    }
};
