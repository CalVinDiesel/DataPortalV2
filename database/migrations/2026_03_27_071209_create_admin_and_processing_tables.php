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
        Schema::create('ClientUploads', function (Blueprint $table) {
            $table->id();
            $table->string('project_id', 128)->index();
            $table->string('project_title', 255)->nullable();
            $table->string('upload_type', 32)->default('single');
            $table->integer('file_count')->default(0);
            $table->json('file_paths')->nullable(); // stored as json for compatibility
            $table->string('camera_models', 512)->nullable();
            $table->date('capture_date')->nullable();
            $table->string('organization_name', 255)->nullable();
            $table->string('created_by_email', 255)->nullable();
            $table->string('request_status', 32)->default('pending')->index();
            $table->text('rejected_reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decided_by', 255)->nullable();
            $table->text('drone_pos_file_path')->nullable();
            $table->decimal('tokens_charged', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('ProcessingRequests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained('ClientUploads')->onDelete('cascade');
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('requested_at')->useCurrent();
            $table->string('requested_by', 255)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('result_tileset_url', 2048)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ProcessingRequests');
        Schema::dropIfExists('ClientUploads');
    }

};
