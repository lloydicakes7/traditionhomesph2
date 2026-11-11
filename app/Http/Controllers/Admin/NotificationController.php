<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;

/**
 * The officer's own notifications: the page shell, the feed the bell polls, and
 * the way one line is opened.
 *
 * The list itself, its paging and "mark all as read" are the page's Livewire
 * component (`App\Http\Livewire\Admin\Notifications\NotificationList`). The feed
 * stays here because the navbar polls a JSON endpoint, not a component.
 *
 * Nothing here is permission-gated, and nothing needs to be — a notification is
 * the officer's own inbox, and every read is bounded by the signed-in account
 * (the component lists `auth()->user()`'s rows, the feed counts them, and a line
 * can only be opened by the account that owns it). What an officer is *told* is
 * decided where the event happens, through the policies — see
 * App\Services\NotificationService.
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
     * Show the notifications page — the shell around the officer's own list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.notifications');
    }

    /**
     * Answer the navbar bell's polling feed — the unread badge and the lines the
     * dropdown shows, in the shape Laravel-AdminLTE's update_cfg expects
     * (config/adminlte.php).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function feed()
    {
        return response()->json(
            $this->notifications->bell(auth()->user(), 'admin.notifications.read')
        );
    }

    /**
     * Open one notification: it is marked read, then its page is opened.
     *
     * The line has to be this officer's own — a notification whose id an officer
     * guessed is a 404, not somebody else's news.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function read($id)
    {
        $notification = auth()->user()->notifications()->whereKey($id)->firstOrFail();

        $notification->markAsRead();

        return redirect($notification->data['url'] ?: route('admin.home'));
    }
}
