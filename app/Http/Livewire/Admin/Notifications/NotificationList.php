<?php

namespace App\Http\Livewire\Admin\Notifications;

use App\Http\Livewire\Notifications\AbstractNotificationList;

/**
 * The officer's own inbox, newest first.
 *
 * Ownership bounds every read, exactly as the controller's page did: the list,
 * the unread count and the read-all action all run against the signed-in
 * officer's own rows — the `web` guard's account.
 */
class NotificationList extends AbstractNotificationList
{
    /**
     * @return \App\Models\Admin
     */
    protected function account()
    {
        return auth('web')->user();
    }

    protected function readRoute(): string
    {
        return 'admin.notifications.read';
    }
}
