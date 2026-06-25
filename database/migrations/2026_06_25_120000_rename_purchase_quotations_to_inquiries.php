<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('purchase_quotations', 'inquiries');
        Schema::table('inquiries', function (Blueprint $table) {
            $table->renameColumn('purchase_id', 'inquiry_id');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->renameColumn('inquiry_id', 'purchase_id');
        });
        Schema::rename('inquiries', 'purchase_quotations');
    }
};
