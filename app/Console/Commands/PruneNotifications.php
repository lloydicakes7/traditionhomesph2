<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

/**
 * The notifications table's retention window.
 *
 * A notification is a line that is worth reading while it is current and worth
 * nothing once it is not: the record it is about is still in the panel and can
 * be opened directly. Keeping the table bounded is therefore deliberate — a
 * month is longer than any answer a homeowner waits on and longer than a
 * billing cycle, and it is how far back the notifications page lets anyone
 * look.
 *
 * It runs daily (see App\Console\Kernel), so the table holds roughly the last
 * month of events and never grows for the life of the install.
 */
class PruneNotifications extends Command
{
    /** How long a notification is kept, in days. */
    const KEEP_DAYS = 30;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications older than their retention window (30 days)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $pruned = DatabaseNotification::query()
            ->where('created_at', '<', now()->subDays(self::KEEP_DAYS))
            ->delete();

        $this->info('Pruned ' . $pruned . ' notification(s) older than ' . self::KEEP_DAYS . ' days.');

        return 0;
    }
}
