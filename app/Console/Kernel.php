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
        // $schedule->command('inspire')->hourly();

        // Auto cleanup old pending transactions every 30 minutes
        $schedule->command('app:cleanup-pending-transactions')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Cleanup very old failed transactions monthly
        $schedule->command('app:cleanup-stale-refunds')
                 ->monthlyOn(1, '02:00')
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
