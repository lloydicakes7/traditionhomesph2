<?php

namespace App\Http\Livewire\Portal\Notifications;

use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\Notifications\AbstractNotificationList;

/**
 * The household's own inbox, newest first.
 *
 * The portal is ownership-based, and so is this: the trait resolves the
 * `homeowner` guard's account (and makes it the request's default guard), so
 * another household's notifications are never in the result set.
 */
class NotificationList extends AbstractNotificationList
{
    use ResolvesHomeownerAccount;

    protected function readRoute(): string
    {
        return 'portal.notifications.read';
    }
}
