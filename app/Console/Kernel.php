<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto refresh pending wallet points gem orders (Shop2TopUp)
        $schedule->command('wallet:refresh-points-orders --limit=50')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/cron-wallet-points.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
