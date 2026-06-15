<?php

// Boot Laravel application context
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\SFTPGoService;
use Illuminate\Support\Facades\Http;

echo "=========================================\n";
echo "SFTPGo Diagnostic Script\n";
echo "=========================================\n";

// Get Super Admin Email
$email = env('SUPER_ADMIN_EMAIL', 'mosestiquan23@gmail.com');
echo "Target Super Admin Email: {$email}\n";

$user = User::where('email', $email)->first();
if (!$user) {
    echo "ERROR: Super Admin user not found in database!\n";
    exit(1);
}

echo "Database sftp_username: " . ($user->sftp_username ?: 'Not set') . "\n";
try {
    $decryptedPassword = $user->sftp_password; // accessing calls decrypted accessor
    echo "Database decrypted sftp_password: " . ($decryptedPassword ?: 'Not set') . "\n";
} catch (\Exception $e) {
    echo "ERROR decrypting sftp_password: " . $e->getMessage() . "\n";
    $decryptedPassword = null;
}

// Test SFTPGo API connection
$apiUrl = rtrim(env('SFTPGO_API_URL'), '/');
$apiUser = env('SFTPGO_ADMIN_USERNAME');
$apiPass = env('SFTPGO_ADMIN_PASSWORD');

echo "\n--- API Settings ---\n";
echo "API URL: {$apiUrl}\n";
echo "API Username: {$apiUser}\n";

if (empty($apiUrl) || empty($apiUser) || empty($apiPass)) {
    echo "ERROR: SFTPGo API credentials are not set in .env!\n";
    exit(1);
}

// Get Access Token
try {
    $tokenResponse = Http::withBasicAuth($apiUser, $apiPass)->get($apiUrl . '/api/v2/token');
    if (!$tokenResponse->successful()) {
        echo "ERROR fetching API token: Status " . $tokenResponse->status() . " - " . $tokenResponse->body() . "\n";
        exit(1);
    }
    $token = $tokenResponse->json('access_token');
    echo "Successfully obtained SFTPGo API JWT token.\n";
} catch (\Exception $e) {
    echo "ERROR connecting to SFTPGo token endpoint: " . $e->getMessage() . "\n";
    exit(1);
}

$client = Http::baseUrl($apiUrl . '/api/v2')->withToken($token)->acceptJson();

// Check user status in SFTPGo
$username = $user->sftp_username;
echo "\n--- Checking User in SFTPGo ---\n";
try {
    $getResponse = $client->get('/users/' . urlencode($username));
    echo "GET /users/{$username} response status: " . $getResponse->status() . "\n";
    if ($getResponse->status() === 404) {
        echo "User does NOT exist in SFTPGo.\n";
    } elseif ($getResponse->successful()) {
        echo "User exists in SFTPGo.\n";
        $existingData = json_decode($getResponse->body(), true);
        echo "Home directory in SFTPGo: " . ($existingData['home_dir'] ?? 'Not set') . "\n";
        echo "Status in SFTPGo: " . ($existingData['status'] ?? 'Not set') . "\n";
    } else {
        echo "Error: " . $getResponse->body() . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR fetching user from SFTPGo: " . $e->getMessage() . "\n";
}

// Execute Sync
echo "\n--- Running SFTPGoService::syncUser() ---\n";
try {
    SFTPGoService::syncUser($user);
    echo "Sync method execution completed. Check your Laravel logs (/storage/logs/laravel.log) for details.\n";
} catch (\Exception $e) {
    echo "ERROR during syncUser: " . $e->getMessage() . "\n";
}

// Check user status in SFTPGo again
echo "\n--- Re-verifying user in SFTPGo after Sync ---\n";
try {
    $getResponse = $client->get('/users/' . urlencode($username));
    if ($getResponse->successful()) {
        echo "User exists in SFTPGo.\n";
        $existingData = json_decode($getResponse->body(), true);
        echo "Updated Home directory: " . ($existingData['home_dir'] ?? 'Not set') . "\n";
        echo "Updated permissions: " . json_encode($existingData['permissions'] ?? []) . "\n";
    } else {
        echo "GET /users/{$username} returned status " . $getResponse->status() . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR re-verifying: " . $e->getMessage() . "\n";
}

echo "=========================================\n";
