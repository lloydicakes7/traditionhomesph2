<?php

namespace App\Http\Livewire\Notifications;

use App\Actions\Notifications\DeleteNotification;
use App\Actions\Notifications\MarkNotificationRead;
use App\Livewire\BaseComponent;
use App\Utility\NotificationRetentionHelper;
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
 * The page draws its own table of Title, Description and Actions, so it can
 * collapse into cards on a small screen; the bell keeps the smaller shared
 * line (resources/views/notifications/item). A notification is kept for one
 * week after it is read (App\Utility\NotificationRetentionHelper), which is
 * the deadline the page writes under its description.
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

        $this->emit('notify', 'success', 'All notifications marked as read.');
    }

    /**
     * Mark one notification read, without leaving the page, and say when it
     * will be deleted.
     *
     * @param  string  $id
     * @return void
     */
    public function markAsRead($id): void
    {
        $notification = app(MarkNotificationRead::class)->handle($this->account(), $id);
        $expiresAt = NotificationRetentionHelper::expiresAt($notification->read_at);

        $this->emit('notify', 'success', $expiresAt
            ? 'Notification marked as read. It will be deleted on '
                . $expiresAt->format('M j, Y') . ' at ' . $expiresAt->format('g:i A') . '.'
            : 'Notification marked as read.');
    }

    /**
     * Delete one notification.
     *
     * @param  string  $id
     * @return void
     */
    public function delete($id): void
    {
        app(DeleteNotification::class)->handle($this->account(), $id);

        $this->emit('notify', 'success', 'Notification deleted.');
    }

    /**
     * Render the account's notifications, newest first.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $account = $this->account();
        $notifications = $account->notifications()->latest()->paginate(self::PER_PAGE);

        return view('livewire.notifications.notification-list', [
            'notifications' => $notifications,
            'readRoute'     => $this->readRoute(),
            'hasUnread'     => $account->unreadNotifications()->exists(),
            'deletions'     => $notifications->getCollection()
                ->filter(function ($notification) {
                    return $notification->read_at !== null;
                })
                ->mapWithKeys(function ($notification) {
                    return [$notification->id => NotificationRetentionHelper::expiresAt($notification->read_at)];
                }),
        ]);
    }
}
