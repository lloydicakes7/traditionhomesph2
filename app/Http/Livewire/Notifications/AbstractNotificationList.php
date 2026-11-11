<?php

namespace App\Http\Livewire\Notifications;

use App\Livewire\BaseComponent;
use Livewire\WithPagination;

/**
 * The account's notifications page, shared by both panels.
 *
 * A notification is an account's own inbox, and the page is one Livewire
 * component per side: each side names its own account and its own read route,
 * and every read is bounded by the signed-in account's rows. The one thing that
 * stays on the controller is the bell's polling feed
 * (`NotificationController::feed`), because the navbar polls a JSON endpoint
 * rather than rendering a component — this class answers the page only.
 *
 * The line itself is the shared one the bell's dropdown also renders
 * (resources/views/notifications/item), so the page and the bell never disagree.
 */
abstract class AbstractNotificationList extends BaseComponent
{
    use WithPagination;

    /** How many notifications one page of the list holds. */
    const PER_PAGE = 20;

    /**
     * The signed-in account whose inbox this is — the officer on the panel, the
     * household on the portal.
     *
     * @return \App\Models\Admin|\App\Models\User
     */
    abstract protected function account();

    /**
     * The route that opens one notification: it marks the line read and
     * redirects to where it points. Opening a line is a redirect rather than a
     * render, so it stays an ordinary link to the controller, not a Livewire call.
     */
    abstract protected function readRoute(): string;

    /**
     * Mark every unread notification read.
     *
     * The re-render that follows is what hides the button and drops each line's
     * unread mark, so no event or redirect is needed.
     *
     * @return void
     */
    public function markAllAsRead(): void
    {
        $this->account()->unreadNotifications->markAsRead();
    }

    /**
     * Render the account's notifications, newest first.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $account = $this->account();

        return view('livewire.notifications.notification-list', [
            'notifications' => $account->notifications()->latest()->paginate(self::PER_PAGE),
            'readRoute'     => $this->readRoute(),
            'hasUnread'     => $account->unreadNotifications()->exists(),
        ]);
    }
}
