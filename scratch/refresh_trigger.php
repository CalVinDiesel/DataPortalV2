<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "--- 🚀 REFRESHING SFTPGO SYNC TRIGGER (v241) ---\n";
    
    $migration = require 'database/migrations/2026_04_16_000000_setup_sftpgo_sync.php';
    $migration->up();
    
    echo "✅ Trigger refreshed successfully.\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
