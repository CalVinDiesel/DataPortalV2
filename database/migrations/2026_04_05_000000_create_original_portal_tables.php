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
        // 1. DataPortalUsers
        if (!Schema::hasTable('DataPortalUsers')) {
            Schema::create('DataPortalUsers', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('email', 255)->unique();
                $table->string('name', 255)->nullable();
                $table->string('password_hash', 255)->nullable();
                $table->string('oauth_id', 255)->nullable();
                $table->string('invitation_token', 255)->nullable();
                $table->timestampTz('invitation_expires_at')->nullable();
                $table->timestampsTz();
            });
        }

        // 2. ClientUploads
        if (!Schema::hasTable('ClientUploads')) {
            Schema::create('ClientUploads', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('project_id', 128);
                $table->string('project_title', 255)->nullable();
                $table->string('upload_type', 32)->default('single');
                $table->integer('file_count')->default(0);
                $table->jsonb('file_paths')->nullable();
                $table->string('camera_models', 512)->nullable();
                $table->date('capture_date')->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->string('created_by_email', 255)->nullable();
                $table->string('request_status', 32)->default('pending');
                $table->text('rejected_reason')->nullable();
                $table->timestampTz('decided_at')->nullable();
                $table->string('decided_by', 255)->nullable();
                $table->text('project_description')->nullable();
                $table->string('category', 128)->nullable();
                $table->double('latitude')->nullable();
                $table->double('longitude')->nullable();
                $table->string('image_metadata', 512)->nullable();
                $table->text('drone_pos_file_path')->nullable();
                $table->bigInteger('total_size_bytes')->default(0);
                $table->decimal('tokens_charged', 12, 2)->nullable();
            });
        }

        // 3. MapData
        if (!Schema::hasTable('MapData')) {
            Schema::create('MapData', function (Blueprint $table) {
                $table->string('mapDataID', 64)->primary();
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->double('xAxis')->nullable();
                $table->double('yAxis')->nullable();
                $table->string('3dTiles', 2048);
                $table->string('thumbNailUrl', 2048)->nullable();
                $table->timestamp('updateDateTime')->nullable();
                $table->decimal('purchase_price_tokens', 12, 2)->nullable();
            });
        }

        // 4. MapDataPurchases
        if (!Schema::hasTable('MapDataPurchases')) {
            Schema::create('MapDataPurchases', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('user_email', 255);
                $table->string('map_data_id', 64);
                $table->decimal('tokens_paid', 12, 2);
                $table->integer('token_transaction_id')->nullable();
                $table->timestampTz('purchased_at')->useCurrent();
            });
        }

        // 5. Showcase
        if (!Schema::hasTable('Showcase')) {
            Schema::create('Showcase', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('map_data_id', 64);
                $table->integer('display_order')->default(0);
                $table->timestampTz('created_at')->useCurrent();
            });
        }

        // 6. StripePayments
        if (!Schema::hasTable('StripePayments')) {
            Schema::create('StripePayments', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('user_email', 255);
                $table->string('stripe_payment_intent_id', 255);
                $table->string('stripe_customer_id', 255)->nullable();
                $table->decimal('amount_myr', 10, 2);
                $table->decimal('tokens_credited', 12, 2);
                $table->string('status', 32)->default('pending');
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->useCurrent();
            });
        }

        // 7. TokenTransactions
        if (!Schema::hasTable('TokenTransactions')) {
            Schema::create('TokenTransactions', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('user_email', 255);
                $table->decimal('amount', 12, 2);
                $table->decimal('balance_after', 12, 2)->nullable();
                $table->string('type', 32);
                $table->string('reference_type', 32)->nullable();
                $table->string('reference_id', 128)->nullable();
                $table->string('stripe_payment_intent_id', 255)->nullable();
                $table->decimal('myr_amount', 10, 2)->nullable();
                $table->jsonb('metadata')->nullable();
                $table->timestampTz('created_at')->useCurrent();
            });
        }

        // 8. TokenWallet
        if (!Schema::hasTable('TokenWallet')) {
            Schema::create('TokenWallet', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('user_email', 255);
                $table->decimal('token_balance', 12, 2)->default(0.00);
                $table->string('stripe_customer_id', 255)->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TokenWallet');
        Schema::dropIfExists('TokenTransactions');
        Schema::dropIfExists('StripePayments');
        Schema::dropIfExists('Showcase');
        Schema::dropIfExists('MapDataPurchases');
        Schema::dropIfExists('MapData');
        Schema::dropIfExists('ClientUploads');
        Schema::dropIfExists('DataPortalUsers');
    }
};
