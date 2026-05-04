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
        echo "Connected to SFTP server via SSH.\n";
        
        // Rename main folders
        $commands = [
            "mv /home/tiquan/uploads /home/tiquan/upload",
            "mv /home/tiquan/deliveries /home/tiquan/delivers",
            "mv /home/tiquan/delivered /home/tiquan/delivers",
        ];
        
        foreach ($commands as $cmd) {
            echo "Executing: $cmd\n";
            echo $ssh->exec($cmd);
        }
        
        echo "Checking new structure:\n";
        echo $ssh->exec("ls -F /home/tiquan/");
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
