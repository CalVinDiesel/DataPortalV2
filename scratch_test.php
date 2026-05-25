<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $portalUser = DB::table('portal_users')->where('sftp_username', 'MosesTiQuan_8viq1ct6')->first();
    echo "PORTAL USER:\n";
    print_r($portalUser ? (array)$portalUser : "Not Found in portal_users");

    $sftpGoUser = DB::table('users')->where('username', 'MosesTiQuan_8viq1ct6')->first();
    echo "\nSFTPGO USER:\n";
    print_r($sftpGoUser ? (array)$sftpGoUser : "Not Found in SFTPGo database");
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
