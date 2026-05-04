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
        
        // Clean up nested folders
        $commands = [
            "mv /home/tiquan/uploads/deliveries /home/tiquan/deliveries_temp",
            "mv /home/tiquan/uploads/uploads/* /home/tiquan/uploads/",
            "rmdir /home/tiquan/uploads/uploads",
            "mv /home/tiquan/deliveries_temp/* /home/tiquan/deliveries/",
            "rmdir /home/tiquan/deliveries_temp"
        ];
        
        foreach ($commands as $cmd) {
            echo "Executing: $cmd\n";
            echo $ssh->exec($cmd);
        }
        
        echo "Final Structure /home/tiquan/:\n";
        echo $ssh->exec("ls -F /home/tiquan/");
        echo "Final Structure /home/tiquan/uploads/:\n";
        echo $ssh->exec("ls -F /home/tiquan/uploads/");
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
