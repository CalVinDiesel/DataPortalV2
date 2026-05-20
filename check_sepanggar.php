<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pin = App\Models\MapData::where('mapDataID', 'Sepanggar-Point')->first();
print_r($pin ? $pin->toArray() : "Not found in database");
