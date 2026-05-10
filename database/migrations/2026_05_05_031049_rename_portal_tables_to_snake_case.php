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
        // 1. Rename Tables
        $renames = [
            'DataPortalUsers' => 'portal_users',
            'ClientUploads' => 'client_uploads',
            'MapData' => 'map_data',
            'MapDataPurchases' => 'map_data_purchases',
            'ProcessingRequests' => 'processing_requests',
            'AccessRequests' => 'access_requests',
            'StripePayments' => 'stripe_payments',
            'TokenTransactions' => 'token_transactions',
            'TokenWallet' => 'token_wallet',
            'Showcase' => 'showcases',
        ];

        foreach ($renames as $old => $new) {
            if (Schema::hasTable($old) && !Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }

        // 2. Rename Column in portal_users
        if (Schema::hasTable('portal_users')) {
            Schema::table('portal_users', function (Blueprint $table) {
                if (Schema::hasColumn('portal_users', 'password_hash') && !Schema::hasColumn('portal_users', 'password')) {
                    $table->renameColumn('password_hash', 'password');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Reverse Column Rename
        if (Schema::hasTable('portal_users')) {
            Schema::table('portal_users', function (Blueprint $table) {
                if (Schema::hasColumn('portal_users', 'password') && !Schema::hasColumn('portal_users', 'password_hash')) {
                    $table->renameColumn('password', 'password_hash');
                }
            });
        }

        // 2. Reverse Table Renames
        $renames = [
            'portal_users' => 'DataPortalUsers',
            'client_uploads' => 'ClientUploads',
            'map_data' => 'MapData',
            'map_data_purchases' => 'MapDataPurchases',
            'processing_requests' => 'ProcessingRequests',
            'access_requests' => 'AccessRequests',
            'stripe_payments' => 'StripePayments',
            'token_transactions' => 'TokenTransactions',
            'token_wallet' => 'TokenWallet',
            'showcases' => 'Showcase',
        ];

        foreach ($renames as $old => $new) {
            if (Schema::hasTable($old) && !Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }
    }
};
