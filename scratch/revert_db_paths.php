<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClientUpload;

$uploads = ClientUpload::all();
foreach ($uploads as $upload) {
    if ($upload->file_paths) {
        $paths = $upload->file_paths;
        $updated = false;
        foreach ($paths as $i => $path) {
            if (str_contains($path, '/upload/')) {
                $paths[$i] = str_replace('/upload/', '/uploads/', $path);
                $updated = true;
            }
        }
        if ($updated) {
            $upload->file_paths = $paths;
            $upload->save();
            echo "Updated paths for project: {$upload->project_id}\n";
        }
    }
}
echo "Database paths reverted to plural.\n";
