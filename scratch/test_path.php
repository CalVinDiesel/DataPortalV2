<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

try {
    $disk = Storage::disk('sftp_delivery');
    echo "Testing access to /uploads/MosesTiQuan_8viq1ct6/kkip-point-cf1i\n";
    if ($disk->exists('/MosesTiQuan_8viq1ct6/kkip-point-cf1i')) {
        echo "Found via relative path /MosesTiQuan_8viq1ct6/...\n";
    } else {
        echo "NOT found via relative path.\n";
    }
    
    // SFTP disks root is /home/tiquan/uploads/
    // So /MosesTiQuan_8viq1ct6/ maps to /home/tiquan/uploads/MosesTiQuan_8viq1ct6/
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
