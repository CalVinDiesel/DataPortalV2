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
        
        // 1. Fix MosesTiQuan_8viq1ct6 specifically
        echo "Moving MosesTiQuan_8viq1ct6 projects...\n";
        echo $ssh->exec("mv /home/tiquan/uploads/uploads/MosesTiQuan_8viq1ct6/* /home/tiquan/uploads/MosesTiQuan_8viq1ct6/");
        
        // 2. Fix other potential nested users
        echo "Moving any other nested users...\n";
        echo $ssh->exec("mv /home/tiquan/uploads/uploads/* /home/tiquan/uploads/");
        
        // 3. Fix nested deliveries
        echo "Moving nested deliveries...\n";
        echo $ssh->exec("mv /home/tiquan/deliveries/deliveries/* /home/tiquan/deliveries/");
        
        // 4. Cleanup
        echo "Cleaning up redundant nested folders...\n";
        echo $ssh->exec("rmdir /home/tiquan/uploads/uploads/MosesTiQuan_8viq1ct6");
        echo $ssh->exec("rmdir /home/tiquan/uploads/uploads");
        echo $ssh->exec("rmdir /home/tiquan/deliveries/deliveries");

        echo "\nFinal Verification /home/tiquan/uploads/MosesTiQuan_8viq1ct6/:\n";
        echo $ssh->exec("ls -F /home/tiquan/uploads/MosesTiQuan_8viq1ct6/");
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
