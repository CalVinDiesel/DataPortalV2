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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 128)->nullable()->after('name');
            $table->string('contact_number', 64)->nullable()->after('email');
            $table->string('role', 32)->default('registered')->after('contact_number');
            $table->string('provider', 32)->default('local')->after('role');
            $table->string('stripe_customer_id', 255)->nullable()->after('provider');
            $table->timestamp('removed_at')->nullable();
            $table->text('removal_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'contact_number', 'role', 'provider', 'stripe_customer_id', 'removed_at', 'removal_reason']);
        });
    }

};
