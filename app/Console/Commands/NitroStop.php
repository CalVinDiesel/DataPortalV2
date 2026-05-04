<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NitroStop extends Command
{
    protected $signature = 'nitro:stop
                            {--workers=16 : Number of parallel upload lanes}
                            {--start-port=9001 : Starting port for upload workers}';

    protected $description = 'Stop all Hyper-Nitro background upload workers';

    public function handle(): void
    {
        $workers   = (int) $this->option('workers');
        $startPort = (int) $this->option('start-port');
        $endPort   = $startPort + $workers - 1;

        $this->newLine();
        $this->info("Stopping Hyper-Nitro workers (ports {$startPort}–{$endPort})...");

        $killed = 0;
        for ($port = $startPort; $port <= $endPort; $port++) {
            // Find and kill PHP processes bound to each worker port
            $pid = shell_exec("netstat -ano | findstr :$port | findstr LISTENING");
            if ($pid) {
                // Extract the PID (last column)
                preg_match('/\s+(\d+)\s*$/', trim($pid), $matches);
                if (!empty($matches[1])) {
                    shell_exec("taskkill /F /PID {$matches[1]} > nul 2>&1");
                    $killed++;
                }
            }
        }

        if ($killed > 0) {
            $this->info("  ✅ Stopped {$killed} workers.");
        } else {
            $this->warn('  ⚠️  No active workers found (already stopped?).');
        }

        $this->newLine();
    }
}
