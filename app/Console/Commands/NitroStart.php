<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NitroStart extends Command
{
    protected $signature = 'nitro:start
                            {--port=8000 : Main portal port}
                            {--workers=16 : Number of parallel upload lanes}
                            {--start-port=9001 : Starting port for upload workers}';

    protected $description = 'Start the Hyper-Nitro parallel upload engine (workers + main portal)';

    public function handle(): void
    {
        $mainPort   = (int) $this->option('port');
        $workers    = (int) $this->option('workers');
        $startPort  = (int) $this->option('start-port');
        $endPort    = $startPort + $workers - 1;
        $publicPath = base_path('public');
        $phpBin     = PHP_BINARY; // uses the same PHP version running artisan

        $this->newLine();
        $this->line('  ╔══════════════════════════════════════════════╗');
        $this->line('  ║   DATA PORTAL V2 — HYPER-NITRO ENGINE        ║');
        $this->line("  ║   {$workers}-Lane Parallel Upload System              ║");
        $this->line('  ╚══════════════════════════════════════════════╝');
        $this->newLine();

        // ── Step 1: Spawn the upload worker fleet ─────────────────────────
        $this->info("[1/2] Launching {$workers} Upload Workers (ports {$startPort}–{$endPort})...");

        $launchedCount = 0;
        for ($port = $startPort; $port <= $endPort; $port++) {
            // Windows: 'start /b' detaches the process from this terminal. 
            // ⚠️ FIX: We must provide empty quotes "" for the title, otherwise start treats the PHP path as the title.
            $cmd = "start /b \"\" \"{$phpBin}\" -S 127.0.0.1:{$port} -t \"{$publicPath}\" > nul 2>&1";
            popen($cmd, 'r');
            $launchedCount++;
        }

        // Give workers a moment to bind to their ports
        sleep(1);

        $this->info("  ✅ {$launchedCount} workers running in the background.");
        $this->newLine();

        // ── Step 2: Start the main portal ─────────────────────────────────
        $this->info("[2/2] Starting Main Portal on http://127.0.0.1:{$mainPort} ...");
        $this->line('  (Press Ctrl+C here to stop the portal. Workers will be cleaned up by nitro:stop)');
        $this->newLine();

        // This blocks until the user presses Ctrl+C — same as 'php artisan serve'
        $this->call('serve', ['--port' => $mainPort]);
    }
}
