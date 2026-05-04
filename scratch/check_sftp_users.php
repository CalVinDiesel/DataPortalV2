<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$cols = Schema::getColumnListing('sftp_users');
echo "Columns in sftp_users: " . implode(', ', $cols) . "\n";

$first = DB::table('sftp_users')->first();
print_r($first);
