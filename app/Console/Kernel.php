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
        $schedule->command('notifications:prune')->dailyAt('02:00');

        // The dues configuration, run on its own. Each of these does nothing
        // unless the officer has asked for it in Settings → Collections: the
        // month is raised only in automatic mode, a penalty only where one is
        // configured, a reminder only where the switch is on.
        $schedule->command('dues:generate-monthly')->dailyAt('06:00');
        $schedule->command('dues:apply-penalties')->dailyAt('06:15');
        $schedule->command('dues:send-reminders')->dailyAt('08:00');
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
