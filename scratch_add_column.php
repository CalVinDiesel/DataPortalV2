<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Schema::table('ClientUploads', function (Blueprint $table) {
    if (!Schema::hasColumn('ClientUploads', 'delivered_file_size')) {
        $table->unsignedBigInteger('delivered_file_size')->nullable()->after('delivered_file_path');
        echo "Column 'delivered_file_size' added.\n";
    } else {
        echo "Column already exists.\n";
    }
});
