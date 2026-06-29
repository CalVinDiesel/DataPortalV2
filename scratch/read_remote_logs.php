<?php

require __DIR__ . '/../vendor/autoload.php';

use phpseclib3\Net\SSH2;

$host = '209.97.173.7';
$port = 22;
$user = 'root';
$pass = 'password';

echo "Connecting to remote host {$host}:{$port}...\n";
$ssh = new SSH2($host, $port);
if (!$ssh->login($user, $pass)) {
    exit("Login failed!\n");
}

echo "Login successful. Reading container logs...\n";
$output = $ssh->exec("docker exec dataportal_app tail -n 50 storage/logs/laravel.log");
echo "--- Laravel Logs ---\n";
echo $output;
echo "--------------------\n";
