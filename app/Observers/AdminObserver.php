<?php

namespace App\Observers;

use App\Models\Admin;
use App\Notifications\AdminWelcomeNotification;
use App\Notifications\OfficerRegisteredNotification;
use App\Services\NotificationService;

class AdminObserver
{
    /** @var \App\Services\NotificationService */
    private $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * An officer account was created — by the office, or by the officer's own
     * registration (which may still be waiting for approval).
     */
    public function created(Admin $admin): void
    {
        $admin->notify(new AdminWelcomeNotification());

        $this->notifications->toOfficers(
            new OfficerRegisteredNotification($admin),
            [$admin, auth()->user()]
        );
    }
}
