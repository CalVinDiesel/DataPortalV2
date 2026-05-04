<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$res = DB::select("SELECT prosrc FROM pg_proc WHERE proname = 'sync_to_sftpgo'");
if (!empty($res)) {
    $src = $res[0]->prosrc;
    $lines = explode("\n", $src);
    foreach ($lines as $i => $line) {
        echo ($i+1) . ": " . $line . "\n";
    }
} else {
    echo "Trigger not found.";
}
