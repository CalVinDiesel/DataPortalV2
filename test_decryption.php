<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

$user = User::whereNotNull('sftp_password')->first();
if ($user) {
    echo "Current sftp_password in DB: " . $user->getRawOriginal('sftp_password') . "\n";
    try {
        echo "Decrypted value: " . $user->sftp_password . "\n";
    } catch (\Exception $e) {
        echo "DECRYPTION FAILED: " . $e->getMessage() . "\n";
        echo "Recommendation: Existing hashes need to be cleared or reset.\n";
    }
} else {
    echo "No users with sftp_password found.\n";
}
