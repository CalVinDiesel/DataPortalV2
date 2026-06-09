<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class NitroCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nitro:cleanup {--hours=24 : Age of orphaned files to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up abandoned Nitro upload chunks and temporary files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $nitroRoot = config('filesystems.disks.nitro.root');
        
        $this->info("🚀 Starting Nitro Cleanup (Older than {$hours} hours)...");
        
        if (!File::exists($nitroRoot)) {
            $this->warn("⚠️ Nitro root directory does not exist: {$nitroRoot}");
            return;
        }

        $now = time();
        $deletedCount = 0;
        $freedBytes = 0;

        // Scan all project folders in the Nitro storage
        $projects = File::directories($nitroRoot);

        foreach ($projects as $projectPath) {
            $lastModified = File::lastModified($projectPath);
            $ageInSeconds = $now - $lastModified;
            $ageInHours = $ageInSeconds / 3600;

            if ($ageInHours >= $hours) {
                $folderName = basename($projectPath);
                $size = $this->getDirSize($projectPath);
                
                $this->line("  - Deleting orphaned project: {$folderName} ({$size} bytes)");
                
                File::deleteDirectory($projectPath);
                $deletedCount++;
                $freedBytes += $size;
            }
        }

        $freedMB = round($freedBytes / 1024 / 1024, 2);
        $this->info("✅ Cleanup Complete! Removed {$deletedCount} folders. Freed {$freedMB} MB.");
        Log::info("Nitro Cleanup: Removed {$deletedCount} folders, freed {$freedMB} MB.");
    }

    private function getDirSize($dir)
    {
        $size = 0;
        foreach (File::allFiles($dir) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }
}
