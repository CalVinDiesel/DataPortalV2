<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// We will use Argon2id which is what SFTPGo prefers
$user = App\Models\User::where('email', 'mosestq-sm22@student.tarc.edu.my')->first();

if ($user) {
    // Generate an Argon2id hash specifically for SFTPGo
    // This is the most professional way to handle this
    $rawPassword = 'C8upAqdini74';
    $user->sftp_password = password_hash($rawPassword, PASSWORD_ARGON2ID);
    $user->save();
    
    echo "SUCCESS: Password for " . $user->email . " updated with Argon2id!\n";
    echo "Try logging into Port 22 with WinSCP now.\n";
} else {
    echo "User not found. Check the email address.\n";
}
