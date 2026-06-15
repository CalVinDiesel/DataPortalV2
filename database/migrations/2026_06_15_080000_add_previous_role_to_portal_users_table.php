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
        if (Schema::hasTable('portal_users')) {
            Schema::table('portal_users', function (Blueprint $table) {
                if (!Schema::hasColumn('portal_users', 'previous_role')) {
                    $table->string('previous_role', 64)->nullable()->after('role');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('portal_users')) {
            Schema::table('portal_users', function (Blueprint $table) {
                if (Schema::hasColumn('portal_users', 'previous_role')) {
                    $table->dropColumn('previous_role');
                }
            });
        }
    }
};
