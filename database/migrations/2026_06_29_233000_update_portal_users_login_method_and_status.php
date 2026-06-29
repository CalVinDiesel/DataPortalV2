<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('portal_users')) {
            Schema::table('portal_users', function (Blueprint $table) {
                if (!Schema::hasColumn('portal_users', 'status')) {
                    $table->string('status', 32)->default('pending')->after('is_active');
                }
                if (!Schema::hasColumn('portal_users', 'login_method')) {
                    $table->string('login_method', 32)->nullable()->after('status');
                }
                if (!Schema::hasColumn('portal_users', 'provider_id')) {
                    $table->string('provider_id', 255)->nullable()->after('login_method');
                }
            });

            // Migrate existing active status
            DB::table('portal_users')->where('is_active', true)->update(['status' => 'active']);
            DB::table('portal_users')->where('is_active', false)->update(['status' => 'pending']);

            // Migrate existing login methods
            DB::table('portal_users')->where('provider', 'local')->update(['login_method' => 'password']);
            DB::table('portal_users')->where('provider', 'google')->update([
                'login_method' => 'google',
                'provider_id' => DB::raw('oauth_id')
            ]);
            DB::table('portal_users')->where('provider', 'microsoft')->update([
                'login_method' => 'microsoft',
                'provider_id' => DB::raw('oauth_id')
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('portal_users')) {
            Schema::table('portal_users', function (Blueprint $table) {
                if (Schema::hasColumn('portal_users', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('portal_users', 'login_method')) {
                    $table->dropColumn('login_method');
                }
                if (Schema::hasColumn('portal_users', 'provider_id')) {
                    $table->dropColumn('provider_id');
                }
            });
        }
    }
};
