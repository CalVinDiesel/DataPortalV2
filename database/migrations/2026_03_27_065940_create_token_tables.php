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
        Schema::create('TokenWallet', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 255)->unique();
            $table->decimal('token_balance', 12, 2)->default(0);
            $table->string('stripe_customer_id', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('TokenTransactions', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 255);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2)->nullable();
            $table->string('type', 32);
            $table->string('reference_type', 32)->nullable();
            $table->string('reference_id', 128)->nullable();
            $table->string('stripe_payment_intent_id', 255)->nullable();
            $table->decimal('myr_amount', 10, 2)->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('StripePayments', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 255);
            $table->string('stripe_payment_intent_id', 255)->unique();
            $table->string('stripe_customer_id', 255)->nullable();
            $table->decimal('amount_myr', 10, 2);
            $table->decimal('tokens_credited', 12, 2);
            $table->string('status', 32)->default('pending');
            $table->timestamps();
        });

        Schema::create('MapDataPurchases', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 255);
            $table->string('map_data_id', 64);
            $table->decimal('tokens_paid', 12, 2);
            $table->unsignedBigInteger('token_transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('MapDataPurchases');
        Schema::dropIfExists('StripePayments');
        Schema::dropIfExists('TokenTransactions');
        Schema::dropIfExists('TokenWallet');
    }

};
