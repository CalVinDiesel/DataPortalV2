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
        Schema::table('disclaimer_acceptances', function (Blueprint $table) {
            // Drop old foreign key constraint that mistakenly pointed to 'users' table
            $table->dropForeign(['user_id']);
            
            // Add the correct foreign key constraint pointing to 'portal_users' table
            $table->foreign('user_id')->references('id')->on('portal_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disclaimer_acceptances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
