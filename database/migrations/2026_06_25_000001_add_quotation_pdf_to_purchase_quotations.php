<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->string('quotation_pdf_path')->nullable()->after('quoted_at');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->dropColumn('quotation_pdf_path');
        });
    }
};
