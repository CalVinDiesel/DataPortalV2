<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 3D model tiles delivery tracking fields to purchase_quotations.
     * - delivery_ready: boolean flag admin sets once files are uploaded to SFTP
     * - delivered_at:   timestamp stamped when delivery_ready is first set to true
     */
    public function up(): void
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->boolean('delivery_ready')->default(false)->after('processing_started_at');
            $table->timestamp('delivered_at')->nullable()->after('delivery_ready');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->dropColumn(['delivery_ready', 'delivered_at']);
        });
    }
};
