<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$data = App\Models\MapData::all();
$out = [];
foreach($data as $d) {
    $out[] = [
        'id' => $d->mapDataID,
        'title' => $d->title,
        'thumbNailUrl' => $d->thumbNailUrl
    ];
}
file_put_contents('test_db_output2.json', json_encode($out, JSON_PRETTY_PRINT));
