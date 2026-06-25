<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->timestamp('disclaimer_accepted_at')->nullable()->after('delivered_at');
            $table->string('disclaimer_ip_address', 45)->nullable()->after('disclaimer_accepted_at');
            $table->text('disclaimer_user_agent')->nullable()->after('disclaimer_ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->dropColumn([
                'disclaimer_accepted_at',
                'disclaimer_ip_address',
                'disclaimer_user_agent'
            ]);
        });
    }
};
