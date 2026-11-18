<?php

namespace App\Actions\Notifications;

use App\Utility\NotificationRetentionHelper;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Deletes the read notifications whose week is over.
 *
 * Unread lines are never touched: they are what the bell is still counting,
 * and they leave the table when their reader has opened or deleted them.
 */
class PruneReadNotifications
{
    /**
     * @return int  How many notifications were deleted.
     */
    public function handle(): int
    {
        return DatabaseNotification::query()
            ->whereNotNull('read_at')
            ->where('read_at', '<=', NotificationRetentionHelper::cutoff())
            ->delete();
    }
}
