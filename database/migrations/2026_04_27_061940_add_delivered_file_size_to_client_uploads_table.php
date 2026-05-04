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
            $table->unsignedBigInteger('delivered_file_size')->nullable()->after('delivered_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ClientUploads', function (Blueprint $table) {
            $table->dropColumn('delivered_file_size');
        });
    }
};
