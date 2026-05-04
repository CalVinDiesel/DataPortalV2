<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use phpseclib3\Net\SSH2;

try {
    $sshPort = (int)config('filesystems.disks.sftp_delivery.port', 2222);
    $ssh = new SSH2(config('filesystems.disks.sftp_delivery.host'), $sshPort);
    if ($ssh->login(config('filesystems.disks.sftp_delivery.username'), config('filesystems.disks.sftp_delivery.password'))) {
        echo "Listing /home/tiquan/uploads/upload/:\n";
        echo $ssh->exec("ls -F /home/tiquan/uploads/upload/");
        echo "\n---\n";
        echo "Listing /home/tiquan/uploads/uploads/:\n";
        echo $ssh->exec("ls -F /home/tiquan/uploads/uploads/");
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
