<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

try {
    $disk = Storage::disk('sftp_delivery');
    echo "Listing MosesTiQuan_8viq1ct6/:\n";
    print_r($disk->directories('MosesTiQuan_8viq1ct6'));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
