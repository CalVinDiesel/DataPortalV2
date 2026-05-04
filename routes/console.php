<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
Schedule::command('nitro:cleanup')->daily();
Schedule::command('nitro:cleanup --hours=1')->hourly(); // Extra safety for short-lived temp files
