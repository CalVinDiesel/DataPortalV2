<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$project = \App\Models\ClientUpload::where('project_title', 'like', '%Sepanggar%')->first();
if ($project) {
    echo "Found client upload:\n";
    print_r($project->toArray());
} else {
    echo "No client upload found matching Sepanggar.\n";
}

$all = \App\Models\ClientUpload::all();
echo "\nTotal client uploads in database: " . $all->count() . "\n";
foreach ($all as $u) {
    echo "- " . $u->project_title . " (Type: " . $u->upload_type . ")\n";
}
