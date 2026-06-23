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
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->string('map_data_id', 64)->nullable()->after('user_email');
            
            // Foreign key pointing to map_data.mapDataID
            $table->foreign('map_data_id')->references('mapDataID')->on('map_data')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->dropForeign(['map_data_id']);
            $table->dropColumn('map_data_id');
        });
    }
};
