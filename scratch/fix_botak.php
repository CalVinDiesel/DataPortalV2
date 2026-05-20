<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Update Database
$row = App\Models\MapData::where('mapDataID', 'Bukit-Botak')->first();
if ($row) {
    $row->xAxis = 116.1558;
    $row->save();
    echo "Successfully updated database record for Bukit-Botak!\n";
} else {
    echo "Database record for Bukit-Botak not found.\n";
}

// 2. Update locations.json
$path = public_path('data/locations.json');
if (file_exists($path)) {
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    $updated = false;
    foreach ($data['locations'] as &$l) {
        if ($l['id'] === 'Bukit-Botak') {
            $l['coordinates']['longitude'] = 116.1558;
            $updated = true;
            break;
        }
    }
    if ($updated) {
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "Successfully updated locations.json record for Bukit-Botak!\n";
    } else {
        echo "Bukit-Botak not found in locations.json.\n";
    }
} else {
    echo "locations.json not found.\n";
}
