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
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('username', 255)->unique();
            $table->integer('status');
            $table->bigInteger('expiration_date');
            $table->string('description', 512)->nullable();
            $table->text('password')->nullable();
            $table->text('public_keys')->nullable();
            $table->text('home_dir');
            $table->bigInteger('uid');
            $table->bigInteger('gid');
            $table->integer('max_sessions');
            $table->bigInteger('quota_size');
            $table->integer('quota_files');
            $table->text('permissions');
            $table->bigInteger('used_quota_size');
            $table->integer('used_quota_files');
            $table->bigInteger('last_quota_update');
            $table->integer('upload_bandwidth');
            $table->integer('download_bandwidth');
            $table->bigInteger('last_login');
            $table->text('filters')->nullable();
            $table->text('filesystem')->nullable();
            $table->text('additional_info')->nullable();
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at');
            $table->string('email', 255)->nullable();
            $table->integer('upload_data_transfer');
            $table->integer('download_data_transfer');
            $table->integer('total_data_transfer');
            $table->bigInteger('used_upload_data_transfer');
            $table->bigInteger('used_download_data_transfer');
            $table->bigInteger('deleted_at');
            $table->bigInteger('first_download');
            $table->bigInteger('first_upload');
            $table->bigInteger('last_password_change');
            $table->integer('role_id')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
