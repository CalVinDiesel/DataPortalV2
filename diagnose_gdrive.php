<?php

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClientUpload;

echo "=== DIAGNOSING GOOGLE DRIVE INTEGRATION ===\n\n";

$config = config('filesystems.disks.google_drive');

echo "[CONFIG] Client ID: " . ($config['clientId'] ? 'Present (Ends with ...' . substr($config['clientId'], -10) . ')' : 'MISSING') . "\n";
echo "[CONFIG] Client Secret: " . ($config['clientSecret'] ? 'Present' : 'MISSING') . "\n";
echo "[CONFIG] Refresh Token: " . ($config['refreshToken'] ? 'Present' : 'MISSING') . "\n";

if (!$config['clientId'] || !$config['clientSecret'] || !$config['refreshToken']) {
    echo "\n[ERROR] Missing Google Drive credentials in .env!\n";
    exit(1);
}

// Initialize Google Client
try {
    echo "\nInitializing Google Client...\n";
    $client = new \Google\Client();
    $client->setClientId($config['clientId']);
    $client->setClientSecret($config['clientSecret']);
    $client->addScope(\Google\Service\Drive::DRIVE_READONLY);
    $client->addScope(\Google\Service\Drive::DRIVE);
    
    $refreshToken = trim($config['refreshToken']);
    $refreshToken = str_replace(["\r", "\n", " "], '', $refreshToken);
    
    echo "Fetching access token using refresh token...\n";
    $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
    
    if (isset($token['error'])) {
        echo "[AUTH ERROR] Google API rejected the refresh token!\n";
        echo "Error: " . $token['error'] . "\n";
        if (isset($token['error_description'])) {
            echo "Description: " . $token['error_description'] . "\n";
        }
        exit(1);
    }
    
    echo "[AUTH SUCCESS] Access token generated successfully.\n";
    
    // Find the latest Google Drive upload
    $upload = ClientUpload::where('upload_type', 'google_drive')
        ->orderBy('id', 'desc')
        ->first();
        
    if (!$upload) {
        echo "\n[INFO] No Google Drive project uploads found in database to test.\n";
        exit(0);
    }
    
    echo "\n[TESTING PROJECT] ID: {$upload->id}, Title: {$upload->project_title}\n";
    echo "[TESTING PROJECT] Link: {$upload->google_drive_link}\n";
    
    $folderId = null;
    if (preg_match('/folders\/([a-zA-Z0-9-_]+)/', $upload->google_drive_link, $matches)) {
        $folderId = $matches[1];
    } elseif (preg_match('/id=([a-zA-Z0-9-_]+)/', $upload->google_drive_link, $matches)) {
        $folderId = $matches[1];
    } elseif (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $upload->google_drive_link, $matches)) {
        $folderId = $matches[1];
    }
    
    if (!$folderId) {
        echo "[ERROR] Could not extract Folder ID from link: {$upload->google_drive_link}\n";
        exit(1);
    }
    
    echo "[TESTING PROJECT] Extracted Folder/File ID: {$folderId}\n";
    
    $service = new \Google\Service\Drive($client);
    
    echo "Querying folder metadata from Google Drive...\n";
    $itemInfo = $service->files->get($folderId, [
        'fields' => 'id, name, size, mimeType',
        'supportsAllDrives' => true
    ]);
    
    echo "[API SUCCESS] Connected to item successfully!\n";
    echo "  Name: " . $itemInfo->getName() . "\n";
    echo "  Mime Type: " . $itemInfo->getMimeType() . "\n";
    echo "  Size: " . ($itemInfo->getSize() ?: 'Folder or unknown') . " bytes\n";
    
    echo "\nScanning folder / checking zip contents...\n";
    $controller = new \App\Http\Controllers\ProjectController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('syncGoogleDriveMetadataInternal');
    $method->setAccessible(true);
    
    $method->invoke($controller, $upload, $folderId);
    
    $upload->refresh();
    echo "\n[SCAN SUCCESS] Metadata sync completed successfully!\n";
    echo "  Synced File Count: {$upload->file_count}\n";
    echo "  Synced File Size: " . $upload->total_size_bytes . " bytes\n";
    
} catch (\Exception $e) {
    echo "\n[EXCEPTION OCCURRED] " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
