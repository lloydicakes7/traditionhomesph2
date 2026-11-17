<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Use our deprecation-tolerant HandleExceptions bootstrapper.
     */
    protected function bootstrappers()
    {
        return array_map(function ($bootstrapper) {
            return $bootstrapper === \Illuminate\Foundation\Bootstrap\HandleExceptions::class
                ? \App\Bootstrap\HandleExceptions::class
                : $bootstrapper;
        }, parent::bootstrappers());
    }

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
        $schedule->command('dues:generate-monthly')->dailyAt('06:00');
        $schedule->command('dues:apply-penalties')->dailyAt('06:15');
        $schedule->command('dues:send-reminders')->dailyAt('08:00');
        $schedule->command('homeowners:send-verification-reminders')->twiceDaily(8, 20);
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
