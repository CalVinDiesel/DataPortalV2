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
        Schema::table('DataPortalUsers', function (Blueprint $table) {
            if (!Schema::hasColumn('DataPortalUsers', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('DataPortalUsers', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('username');
            }
            if (!Schema::hasColumn('DataPortalUsers', 'role')) {
                $table->string('role')->default('registered')->after('contact_number');
            }
            if (!Schema::hasColumn('DataPortalUsers', 'provider')) {
                $table->string('provider')->default('local')->after('role');
            }
            if (!Schema::hasColumn('DataPortalUsers', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('DataPortalUsers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('stripe_customer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('DataPortalUsers', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('DataPortalUsers', 'username') ? 'username' : null,
                Schema::hasColumn('DataPortalUsers', 'contact_number') ? 'contact_number' : null,
                Schema::hasColumn('DataPortalUsers', 'role') ? 'role' : null,
                Schema::hasColumn('DataPortalUsers', 'provider') ? 'provider' : null,
                Schema::hasColumn('DataPortalUsers', 'stripe_customer_id') ? 'stripe_customer_id' : null,
                Schema::hasColumn('DataPortalUsers', 'is_active') ? 'is_active' : null,
            ]));
        });
    }
};
