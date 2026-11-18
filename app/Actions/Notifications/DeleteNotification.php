<?php

namespace App\Actions\Notifications;

/**
 * Deletes one notification.
 *
 * The id is looked up on the account's own notifications, so one account can
 * never remove another's line.
 */
class DeleteNotification
{
    /**
     * @param  \App\Models\Admin|\App\Models\User  $account
     */
    public function handle($account, string $id): void
    {
        $account->notifications()->whereKey($id)->firstOrFail()->delete();
    }
}
