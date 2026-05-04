<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$users = DB::table('users')->select('username', 'description')->get();
foreach ($users as $user) {
    echo "User: " . str_pad($user->username, 25) . " | Description: " . $user->description . "\n";
}
