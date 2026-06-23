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
        Schema::table('purchase_quotations', function (Blueprint $table) {
            // Admin-facing fields
            $table->text('admin_notes')->nullable()->after('status');
            $table->text('rejection_reason')->nullable()->after('admin_notes');

            // Quotation pricing
            $table->decimal('quoted_price', 12, 2)->nullable()->after('rejection_reason');
            $table->timestamp('quoted_at')->nullable()->after('quoted_price');

            // Bank payment details (stored per-quotation for flexibility)
            $table->string('bank_name')->nullable()->after('quoted_at');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
            $table->date('payment_deadline')->nullable()->after('bank_account_name');

            // Processing timestamp
            $table->timestamp('processing_started_at')->nullable()->after('payment_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->dropColumn([
                'admin_notes',
                'rejection_reason',
                'quoted_price',
                'quoted_at',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'payment_deadline',
                'processing_started_at',
            ]);
        });
    }
};
