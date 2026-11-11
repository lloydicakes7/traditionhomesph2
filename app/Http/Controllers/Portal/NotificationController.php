<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;

/**
 * The homeowner's own notifications: the page shell, the feed the bell polls,
 * and the way one line is opened.
 *
 * The list itself, its paging and "mark all as read" are the page's Livewire
 * component (`App\Http\Livewire\Portal\Notifications\NotificationList`). The feed
 * stays here because the navbar polls a JSON endpoint, not a component.
 *
 * The portal is ownership-based, and so is this: every read is bounded by the
 * signed-in account's own rows (see App\Models\User and the Notifiable trait),
 * so another household's notifications are never in the result set and one can
 * never be opened by guessing its id. What a homeowner is *told* is decided
 * where the event happens — an officer's write on their own record, never their
 * own doing (App\Services\NotificationService).
 */
class NotificationController extends Controller
{
    /** @var NotificationService */
    private $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * Show the notifications page — the shell around the household's own list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('portal.notifications');
    }

    /**
     * Answer the navbar bell's polling feed — the unread badge and the lines the
     * dropdown shows, in the shape Laravel-AdminLTE's update_cfg expects
     * (config/client.php).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function feed()
    {
        return response()->json(
            $this->notifications->bell(auth('homeowner')->user(), 'portal.notifications.read')
        );
    }

    /**
     * Open one notification: it is marked read, then its page is opened.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function read($id)
    {
        $account = auth('homeowner')->user();

        $notification = $account->notifications()->whereKey($id)->firstOrFail();

        $notification->markAsRead();

        return redirect($notification->data['url'] ?: route('portal.dashboard'));
    }
}
