<?php

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClientUpload;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

echo "=== SFTP CLEANUP: DELETING ORPHANED PROJECT FOLDERS ===\n\n";

$sftpDisk = Storage::disk('sftp_delivery');

// 1. Get all active project IDs from database
$activeProjectIds = ClientUpload::pluck('project_id')->filter()->toArray();
echo "Found " . count($activeProjectIds) . " active projects in database.\n";
if (empty($activeProjectIds)) {
    echo "Warning: No active projects found in database. Proceeding with caution...\n";
}

// 2. List all user directories inside 'uploads/'
if (!$sftpDisk->exists('uploads')) {
    echo "Directory 'uploads' does not exist on SFTP server. Nothing to clean.\n";
    exit(0);
}

$userDirectories = $sftpDisk->directories('uploads');

$orphanedCount = 0;
$scannedCount = 0;

foreach ($userDirectories as $userDir) {
    // $userDir is e.g. "uploads/moses-ti-quan_hib7mj"
    $sftpUsername = basename($userDir);
    echo "Scanning user folder: {$sftpUsername}...\n";
    
    $projectDirectories = $sftpDisk->directories($userDir);
    
    foreach ($projectDirectories as $projectDir) {
        // $projectDir is e.g. "uploads/moses-ti-quan_hib7mj/alam-mesra-poin-p8ab"
        $projectId = basename($projectDir);
        $scannedCount++;
        
        // Skip hidden folders or special files starting with dot
        if (str_starts_with($projectId, '.')) {
            continue;
        }
        
        // Check if project exists in database
        if (!in_array($projectId, $activeProjectIds)) {
            echo "  [ORPHANED] Folder '{$projectId}' has no database record. Deleting...\n";
            try {
                $sftpDisk->deleteDirectory($projectDir);
                echo "  [DELETED] Successfully deleted SFTP folder: {$projectDir}\n";
                $orphanedCount++;
            } catch (\Exception $e) {
                echo "  [ERROR] Failed to delete {$projectDir}: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  [ACTIVE] Folder '{$projectId}' is active.\n";
        }
    }
}

echo "\n=== CLEANUP COMPLETE ===\n";
echo "Scanned {$scannedCount} project directories.\n";
echo "Deleted {$orphanedCount} orphaned project directories.\n";
