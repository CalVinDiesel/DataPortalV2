<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

try {
    $sshPort = (int)config('filesystems.disks.sftp_delivery.port', 2222);
    $ssh = new \phpseclib3\Net\SSH2(config('filesystems.disks.sftp_delivery.host'), $sshPort);
    if ($ssh->login(config('filesystems.disks.sftp_delivery.username'), config('filesystems.disks.sftp_delivery.password'))) {
        echo "Listing /home/tiquan/:\n";
        echo $ssh->exec("ls -F /home/tiquan/");
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
