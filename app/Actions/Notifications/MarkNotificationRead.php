<?php

namespace App\Actions\Notifications;

use Illuminate\Notifications\DatabaseNotification;

/**
 * Marks one notification read.
 *
 * The id is looked up on the account's own notifications, so a line another
 * account owns is a 404 rather than somebody else's news.
 */
class MarkNotificationRead
{
    /**
     * @param  \App\Models\Admin|\App\Models\User  $account
     * @return \Illuminate\Notifications\DatabaseNotification
     */
    public function handle($account, string $id): DatabaseNotification
    {
        $notification = $account->notifications()->whereKey($id)->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return $notification;
    }
}
