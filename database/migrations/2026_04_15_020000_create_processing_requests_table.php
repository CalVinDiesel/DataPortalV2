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
        if (!Schema::hasTable('ProcessingRequests')) {
            Schema::create('ProcessingRequests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('upload_id');
                $table->string('status')->default('processing'); // processing, completed, failed
                $table->timestamp('requested_at')->nullable();
                $table->string('requested_by')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->string('delivery_notes')->nullable();
                $table->string('result_tileset_url')->nullable();
                $table->text('notes')->nullable();

                $table->foreign('upload_id')
                    ->references('id')
                    ->on('ClientUploads')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ProcessingRequests');
    }
};
