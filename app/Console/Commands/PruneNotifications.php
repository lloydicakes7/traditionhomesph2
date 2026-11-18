<?php

namespace App\Console\Commands;

use App\Actions\Notifications\PruneReadNotifications;
use App\Utility\NotificationRetentionHelper;
use Illuminate\Console\Command;

/**
 * The notifications table's retention window.
 *
 * A notification is worth reading while it is current and worth nothing once
 * it is not: the record it is about can be opened directly in the panel. A
 * line is therefore kept for one week after it is read, and an unread line is
 * kept until its reader has seen it.
 *
 * It runs nightly (see App\Console\Kernel), so the table holds one week of
 * read history and never grows for the life of the install.
 */
class PruneNotifications extends Command
{
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
    protected $description = 'Delete notifications kept one week after they were read';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $pruned = app(PruneReadNotifications::class)->handle();

        $this->info(
            'Deleted ' . $pruned . ' notification(s) read more than '
            . NotificationRetentionHelper::READ_DAYS . ' days ago.'
        );

        return 0;
    }
}
