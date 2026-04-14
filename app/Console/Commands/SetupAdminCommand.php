<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetupAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Applies the default super admin credentials defined in .env into the DataPortalUsers table.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Read defaults from the .env environment, or establish fallbacks
        $email = env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');
        $password = env('SUPER_ADMIN_PASSWORD', 'admin123');
        $username = env('SUPER_ADMIN_USER', 'superadmin');

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->role = 'superadmin';
            $user->is_active = true; 
            
            // Ensure SFTP credentials exist
            if (empty($user->sftp_username)) {
                $user->sftp_username = Str::replace(' ', '', $user->name) . '_' . Str::lower(Str::random(8));
            }
            if (empty($user->sftp_password)) {
                $user->sftp_password = Str::random(12);
            }

            // In case the developer wants to auto-reset the password when updating env
            if (env('SUPER_ADMIN_FORCE_PASSWORD', false)) {
                $user->password = Hash::make($password);
            }
            $user->save();
            $this->info("Successfully updated existing user ({$email}) to superadmin role.");
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'password' => Hash::make($password),
                'role' => 'superadmin',
                'is_active' => true,
                'provider' => 'local',
                'sftp_username' => Str::replace(' ', '', $name) . '_' . Str::lower(Str::random(8)),
                'sftp_password' => Str::random(12),
            ]);
            $this->info("Successfully created superadmin account ({$email}).");
        }
    }
}
