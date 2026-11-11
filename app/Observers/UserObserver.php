<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\HomeownerWelcomeNotification;

/**
 * A portal account's first moment.
 *
 * It is written as an observer rather than in the service that issues the
 * account so that *every* path that creates one — the office's form, a resident
 * profiled with an account, a factory in a test — gives the account the same
 * start, and no future path can forget to.
 */
class UserObserver
{
    /**
     * An account was issued: it starts with something in its bell.
     *
     * Only an account that may sign in is welcomed. A disabled account hears
     * nothing until it is enabled, and a self-registration waiting for the
     * office is welcomed by AcceptRegistration once it is accepted.
     */
    public function created(User $user): void
    {
        if (! $user->is_active || $user->isPendingRegistration()) {
            return;
        }

        $user->notify(new HomeownerWelcomeNotification());
    }
}
