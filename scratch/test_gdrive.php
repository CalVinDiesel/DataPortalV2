<?php
require 'vendor/autoload.php';

// Manually load .env since we are running a standalone script
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
        list($name, $value) = explode('=', $line, 2);
        $value = trim($value, '"\'');
        putenv("$name=$value");
    }
}

$clientId = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$refreshToken = getenv('GOOGLE_REFRESH_TOKEN');

echo "Testing GDrive with ClientID: " . substr($clientId, 0, 10) . "...\n";

$client = new \Google\Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->addScope(\Google\Service\Drive::DRIVE_READONLY);

try {
    $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
    if (isset($token['error'])) {
        die("Token Error: " . json_encode($token) . "\n");
    }
    
    $service = new \Google\Service\Drive($client);
    $results = $service->files->listFiles(['pageSize' => 1, 'fields' => 'files(id, name)']);
    
    echo "Success! Found " . count($results->getFiles()) . " files.\n";
    if (count($results->getFiles()) > 0) {
        echo "First file: " . $results->getFiles()[0]->getName() . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
