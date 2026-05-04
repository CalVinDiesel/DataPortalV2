<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "--- 🚀 SFTPGO ROLE LABELING (v241) ---\n";
    
    // 1. Get all users from sftp_users (these are definitely clients)
    $clientUsernames = DB::table('sftp_users')->pluck('user')->toArray();
    
    // 2. Get all users from SFTPGo's main table
    $allUsers = DB::table('users')->get();
    
    $updatedCount = 0;
    foreach ($allUsers as $user) {
        $isClient = in_array($user->username, $clientUsernames);
        $role = $isClient ? 'Role: Client' : 'Role: Admin';
        
        // Build the new description
        // Keep existing info but ensure role is present
        $existingDesc = $user->description ?: '';
        if (strpos($existingDesc, 'Role:') === false) {
            $newDesc = trim($existingDesc . " | " . $role, " | ");
            
            DB::table('users')
                ->where('id', $user->id)
                ->update(['description' => $newDesc]);
                
            echo "✅ Updated {$user->username}: {$newDesc}\n";
            $updatedCount++;
        } else {
            echo "⏭️  Skipping {$user->username} (Already labeled)\n";
        }
    }
    
    echo "--- Done! Updated $updatedCount users. ---\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
