<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pins = \App\Models\MapData::all();
foreach ($pins as $pin) {
    echo "ID: " . $pin->mapDataID . "\n";
    echo "Title: " . $pin->title . "\n";
    echo "Thumbnail in DB: " . $pin->getRawOriginal('thumbNailUrl') . "\n";
    echo "Thumbnail after Rewrite: " . $pin->thumbNailUrl . "\n";
    echo "----------------------------------------\n";
}
