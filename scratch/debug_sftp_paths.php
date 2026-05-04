<?php
require __DIR__ . '/../vendor/autoload.php';

$ssh = new \phpseclib3\Net\SSH2('172.21.107.151', 2222);
if (!$ssh->login('tiquan', 'ubuntu23')) {
    die("Login Failed\n");
}

echo "Login Successful (Port 2222)\n";
echo "PWD: " . $ssh->exec('pwd') . "\n";
echo "Listing Root (/):\n";
echo $ssh->exec('ls -la /');

echo "\nTesting du -sb on moses-ti-quan-kolombong-point-2twn:\n";
echo $ssh->exec("du -sb moses-ti-quan-kolombong-point-2twn");
echo "\nTesting du -sb on /moses-ti-quan-kolombong-point-2twn:\n";
echo $ssh->exec("du -sb /moses-ti-quan-kolombong-point-2twn");
