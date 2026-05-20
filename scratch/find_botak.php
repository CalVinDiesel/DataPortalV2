<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = App\Models\MapData::where('mapDataID', 'Bukit-Botak')->first();
if ($row) {
    echo "Database Record:\n";
    print_r($row->toArray());
} else {
    echo "Database Record: NOT FOUND\n";
}

$path = public_path('data/locations.json');
if (file_exists($path)) {
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    $loc = null;
    foreach ($data['locations'] as $l) {
        if ($l['id'] === 'Bukit-Botak') {
            $loc = $l;
            break;
        }
    }
    if ($loc) {
        echo "\nlocations.json Record:\n";
        print_r($loc);
    } else {
        echo "\nlocations.json Record: NOT FOUND\n";
    }
}
