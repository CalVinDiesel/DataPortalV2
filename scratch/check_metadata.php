<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$project = \App\Models\ClientUpload::where('project_title', 'Sepanggar Point')->first();
if ($project) {
    echo "ID: " . $project->id . "\n";
    echo "Title: " . $project->project_title . "\n";
    echo "Upload Type: " . $project->upload_type . "\n";
    echo "File Count: " . $project->file_count . "\n";
    echo "Metadata: " . $project->image_metadata . "\n";
} else {
    echo "Project not found.\n";
}
