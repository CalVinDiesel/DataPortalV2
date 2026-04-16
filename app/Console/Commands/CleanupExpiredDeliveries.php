<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredDeliveries extends Command
{
    protected $signature = 'app:cleanup-expired-deliveries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes processed 3D models from SFTP storage after they expire (1 week limit).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired deliveries...');

        $expiredUploads = \App\Models\ClientUpload::where('delivered_expires_at', '<', now())
            ->whereNotNull('delivered_file_path')
            ->get();

        if ($expiredUploads->isEmpty()) {
            $this->info('No expired deliveries found.');
            return;
        }

        foreach ($expiredUploads as $upload) {
            $this->info("Processing Upload ID: {$upload->id} - Project: {$upload->project_id}");

            if ($upload->delivered_file_path && Storage::disk('sftp_delivery')->exists($upload->delivered_file_path)) {
                Storage::disk('sftp_delivery')->delete($upload->delivered_file_path);
                $this->info("Deleted file: {$upload->delivered_file_path}");
            }

            // Also cleanup sftp_delivery_path if set
            if ($upload->sftp_delivery_path && Storage::disk('sftp_delivery')->exists($upload->sftp_delivery_path)) {
                Storage::disk('sftp_delivery')->delete($upload->sftp_delivery_path);
            }

            // Update DB record
            $upload->update([
                'delivered_file_path' => null,
                'sftp_delivery_path' => null,
                // We keep delivered_at for historical records, but file is gone.
            ]);
        }

        $this->info('Cleanup completed.');
    }
}
