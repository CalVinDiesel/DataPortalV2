<?php

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

$email = isset($argv[1]) ? $argv[1] : null;

if (!$email) {
    echo "Usage: php diagnose_sftpgo.php [user_email]\n";
    exit(1);
}

echo "=== DIAGNOSING USER: {$email} ===\n\n";

// 1. Fetch from Database
$user = User::where('email', $email)->first();
if (!$user) {
    echo "[DB ERROR] User not found in database.\n";
    exit(1);
}

echo "[DB] ID: {$user->id}\n";
echo "[DB] Name: {$user->name}\n";
echo "[DB] Role: {$user->role}\n";
echo "[DB] Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
echo "[DB] SFTP Username: " . ($user->sftp_username ?: 'None') . "\n";

$decryptedPassword = null;
if ($user->sftp_username) {
    try {
        $decryptedPassword = $user->sftp_password;
        echo "[DB] Decrypted SFTP Password: {$decryptedPassword}\n";
    } catch (\Exception $e) {
        echo "[DB ERROR] Failed to decrypt SFTP password: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// 2. Fetch from SFTPGo API
$apiUrl = env('SFTPGO_API_URL');
$adminUser = env('SFTPGO_ADMIN_USERNAME');
$adminPass = env('SFTPGO_ADMIN_PASSWORD');

if (empty($apiUrl) || empty($adminUser) || empty($adminPass)) {
    echo "[CONFIG ERROR] SFTPGo API configurations (SFTPGO_API_URL, SFTPGO_ADMIN_USERNAME, SFTPGO_ADMIN_PASSWORD) are missing in .env!\n";
    exit(1);
}

echo "[CONFIG] API URL: {$apiUrl}\n";
echo "[CONFIG] Admin User: {$adminUser}\n";
echo "\n";

echo "Connecting to SFTPGo API...\n";
$apiUrl = rtrim($apiUrl, '/');
$tokenResponse = Http::withBasicAuth($adminUser, $adminPass)->get($apiUrl . '/api/v2/token');

if (!$tokenResponse->successful()) {
    echo "[SFTPGO ERROR] Failed to get API token. Status: " . $tokenResponse->status() . " Body: " . $tokenResponse->body() . "\n";
    exit(1);
}

$token = $tokenResponse->json('access_token');
echo "[SFTPGO] Got JWT Token successfully.\n";

if (!$user->sftp_username) {
    echo "[SFTPGO] Skipping SFTPGo check because username is missing in DB.\n";
    exit(0);
}

$userResponse = Http::withToken($token)->acceptJson()->get($apiUrl . '/api/v2/users/' . urlencode($user->sftp_username));

if ($userResponse->status() === 404) {
    echo "[SFTPGO ERROR] User '{$user->sftp_username}' DOES NOT EXIST in SFTPGo!\n";
    echo "Running self-healing sync now...\n";
    
    \App\Services\SFTPGoService::syncUser($user);
    
    // Check again
    $userResponse = Http::withToken($token)->acceptJson()->get($apiUrl . '/api/v2/users/' . urlencode($user->sftp_username));
    if ($userResponse->successful()) {
        echo "[SFTPGO SUCCESS] User was missing, but has now been synced and created successfully!\n";
    } else {
        echo "[SFTPGO ERROR] Self-healing sync failed. Status: " . $userResponse->status() . "\n";
    }
} elseif ($userResponse->successful()) {
    $sftpgoUser = $userResponse->json();
    echo "[SFTPGO] User found!\n";
    echo "[SFTPGO] Status: " . ($sftpgoUser['status'] == 1 ? 'Enabled' : 'Disabled') . "\n";
    echo "[SFTPGO] Home Directory: " . $sftpgoUser['home_dir'] . "\n";
    echo "[SFTPGO] Permissions: " . json_encode($sftpgoUser['permissions']) . "\n";
    
    // Check if password matches
    echo "\nTesting credentials against SFTPGo auth...\n";
    // SFTPGo has an authenticate endpoint to verify if credentials are correct
    $authResponse = Http::withToken($token)->post($apiUrl . '/api/v2/users/authenticate', [
        'username' => $user->sftp_username,
        'password' => $decryptedPassword
    ]);
    
    if ($authResponse->successful()) {
        echo "[AUTH SUCCESS] SFTPGo accepted the username and password!\n";
    } else {
        echo "[AUTH FAILED] SFTPGo rejected these credentials. Status: " . $authResponse->status() . " Body: " . $authResponse->body() . "\n";
        echo "Updating password in SFTPGo to match the database now...\n";
        
        \App\Services\SFTPGoService::syncUser($user);
        
        // Re-auth check
        $authResponse = Http::withToken($token)->post($apiUrl . '/api/v2/users/authenticate', [
            'username' => $user->sftp_username,
            'password' => $decryptedPassword
        ]);
        if ($authResponse->successful()) {
            echo "[AUTH SUCCESS] Password successfully updated and authenticated in SFTPGo!\n";
        } else {
            echo "[AUTH FAILED] Retrying authentication still failed after update. Status: " . $authResponse->status() . "\n";
        }
    }
} else {
    echo "[SFTPGO ERROR] Failed to fetch user details. Status: " . $userResponse->status() . " Body: " . $userResponse->body() . "\n";
}
echo "\n=== DIAGNOSIS COMPLETE ===\n";
