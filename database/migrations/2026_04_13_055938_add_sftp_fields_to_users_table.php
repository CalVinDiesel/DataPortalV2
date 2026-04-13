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
        Schema::table('DataPortalUsers', function (Blueprint $table) {
            $table->string('sftp_username')->nullable()->after('username');
            $table->string('sftp_password')->nullable()->after('sftp_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('DataPortalUsers', function (Blueprint $table) {
            $table->dropColumn(['sftp_username', 'sftp_password']);
        });
    }
};
