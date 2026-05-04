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
        echo $ssh->exec("ls -la /home/tiquan/");
        echo "\n---\n";
        echo $ssh->exec("ls -la /home/tiquan/uploads/");
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
