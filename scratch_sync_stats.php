<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 Recalculating Project Stats...\n";
$uploads = \App\Models\ClientUpload::all();
foreach ($uploads as $u) {
    $projectId = $u->project_id;
    // Get from ENV or fallback
    $root = env('NITRO_STORAGE_ROOT', 'C:/DataPortal_Nitro_Storage');
    $path = rtrim($root, '/') . '/' . $projectId;
    
    if (file_exists($path)) {
        $size = 0;
        $files = 0;
        
        $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $dir = new RecursiveIteratorIterator($it);
        
        foreach ($dir as $file) {
            if ($file->isFile()) {
                // Ignore slots if they still exist
                if (strpos($file->getFilename(), '.slot') !== false) continue;
                $size += $file->getSize();
                $files++;
            }
        }
        
        $u->update([
            'total_bytes' => $size,
            'file_count'  => $files
        ]);
        
        $mb = round($size / 1024 / 1024, 2);
        echo "  - Project {$projectId}: {$mb} MB ({$files} files)\n";
    } else {
        echo "  - Project {$projectId}: Directory not found at {$path}\n";
    }
}
echo "✅ All stats synchronized.\n";
