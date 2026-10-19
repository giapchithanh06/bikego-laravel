<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Summarise the Top Bikers of the previous month at 00:00 on the first day of every month.
     * Development: php artisan schedule:work   |   Production cron: * * * * * php artisan schedule:run
     * Manual run:  php artisan bikego:summarize-monthly [--month=2026-09]
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('bikego:summarize-monthly')->monthlyOn(1, '00:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
