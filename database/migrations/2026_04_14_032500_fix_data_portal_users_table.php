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
            if (Schema::hasColumn('DataPortalUsers', 'password_hash') && !Schema::hasColumn('DataPortalUsers', 'password')) {
                $table->renameColumn('password_hash', 'password');
            }
            if (!Schema::hasColumn('DataPortalUsers', 'remember_token')) {
                $table->rememberToken();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('DataPortalUsers', function (Blueprint $table) {
            if (Schema::hasColumn('DataPortalUsers', 'password') && !Schema::hasColumn('DataPortalUsers', 'password_hash')) {
                $table->renameColumn('password', 'password_hash');
            }
            $table->dropColumn('remember_token');
        });
    }
};
