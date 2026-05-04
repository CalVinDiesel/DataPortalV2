<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClientUpload;

use App\Http\Controllers\ProjectController;

$admin = \App\Models\User::where('email', 'mosestq-sm22@student.tarc.edu.my')->first();
\Auth::setUser($admin);

$uploads = ClientUpload::where('created_by_email', 'mosestq-sm22@student.tarc.edu.my')->get();
echo "Index API Data Check:\n";
foreach ($uploads as $u) {
    echo "ID: {$u->id} | Title: {$u->project_title} | Photos: {$u->file_count} | Size: {$u->total_size_bytes}\n";
}
echo "\nTotal DB Photos: " . $uploads->sum('file_count') . "\n";
echo "TOTAL SIZE: " . $uploads->sum('total_size_bytes') . "\n";
echo "TOTAL PROJECTS: " . $uploads->count() . "\n";
