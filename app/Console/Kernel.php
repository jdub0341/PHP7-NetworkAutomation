<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        \Log::info(__NAMESPACE__.__CLASS__, ['function' => __FUNCTION__, 'state' => 'started']);   // Create log entry.

        // Run the Scout indexing
        $schedule->command('scout:import App\\\Device\\\Device')->dailyAt('02:00')->environments(['staging', 'production'])->withoutOverlapping();

        // Run Netman Device Scan Hourly
        $schedule->command('netman:scanDevice')->hourly()->environments(['staging', 'production'])->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
