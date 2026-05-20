<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mapData = App\Models\MapData::all()->toArray();
echo "--- Map Data DB ---\n";
print_r($mapData);

$jsonPath = public_path('data/locations.json');
if (file_exists($jsonPath)) {
    $json = file_get_contents($jsonPath);
    $data = json_decode($json, true);
    echo "\n--- locations.json ---\n";
    print_r($data);
} else {
    echo "\nlocations.json not found\n";
}
