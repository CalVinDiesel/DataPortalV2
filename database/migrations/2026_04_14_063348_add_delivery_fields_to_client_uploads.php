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
            $table->string('delivery_method')->nullable()->comment('portal, sftp, google_drive');
            $table->string('sftp_delivery_path')->nullable();
            $table->string('gdrive_delivery_folder_id')->nullable();
            $table->string('delivered_file_path')->nullable();
            $table->timestamp('delivered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ClientUploads', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_method',
                'sftp_delivery_path',
                'gdrive_delivery_folder_id',
                'delivered_file_path',
                'delivered_at'
            ]);
        });
    }
};
