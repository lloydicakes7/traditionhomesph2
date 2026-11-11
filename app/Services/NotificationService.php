<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use App\Notifications\OfficerNotification;
use App\Notifications\ThhaNotification;
use Illuminate\Support\Facades\Gate;

/**
 * Who is told what — the one place the app decides the recipients of an event.
 *
 * A notification is always sent from the write path that owns the record it is
 * about (a Service or an observer), and this service is what that call site
 * asks: the two audiences are the two sides of the app, and neither ever hears
 * about its own doing.
 *
 *   — officers are told about the work in front of them: an event names the
 *     ability its page is gated on, and every officer is asked through the Gate
 *     for exactly that ability, so a notification can never reach somebody the
 *     page is closed to. The officer who caused the event is left out — nobody
 *     is told about their own work.
 *   — homeowners are told about their own records only: the record names its
 *     account, and that account is the only recipient. A homeowner filing,
 *     paying or booking is not a trigger here; the office's answer is.
 *
 * The rest of the class serves the bell: what the navbar's polling feed
 * answers, in the shape Laravel-AdminLTE's update_cfg expects (see
 * config/adminlte.php and config/client.php).
 */
class NotificationService
{
    /** How many notifications the bell's dropdown lists. */
    const BELL_LIMIT = 5;

    /**
     * Tell the officers who may act on an event, and only them.
     *
     * @param  array<int, \App\Models\Admin|null>  $except  The officer(s) the
     *         event is not news to: whoever caused it.
     * @return int  How many officers were told.
     */
    public function toOfficers(OfficerNotification $notification, array $except = []): int
    {
        [$ability, $model] = $notification->governedBy();

        $recipients = Admin::with('role')
            ->where('is_active', true)
            ->get()
            ->reject(function (Admin $officer) use ($except) {
                foreach ($except as $excluded) {
                    if ($excluded instanceof Admin && $officer->is($excluded)) {
                        return true;
                    }
                }

                return false;
            })
            ->filter(function (Admin $officer) use ($ability, $model) {
                return Gate::forUser($officer)->allows($ability, $model);
            });

        return $this->send($recipients, $notification);
    }

    /**
     * Tell one homeowner about their own record.
     *
     * @param  \App\Models\User|null  $account  The account the record belongs to
     */
    public function toHomeowner(?User $account, ThhaNotification $notification): void
    {
        $account?->notify($notification);
    }

    /**
     * Tell every homeowner with a portal account — what an announcement is.
     *
     * @return int  How many accounts were told.
     */
    public function toHomeowners(ThhaNotification $notification): int
    {
        $sent = 0;

        User::query()
            ->where('is_active', true)
            ->select('id')
            ->chunkById(200, function ($accounts) use ($notification, &$sent) {
                foreach ($accounts as $account) {
                    $account->notify($notification);
                    $sent++;
                }
            });

        return $sent;
    }

    /**
     * What the navbar bell's polling feed answers.
     *
     * Laravel-AdminLTE reads `label` (the badge), `label_color`, `icon_color`
     * and `dropdown` (the menu's own HTML) off the feed's JSON — see the
     * package's navbar-notification view. The rows are the same lines the
     * notifications page lists, so the bell and the page never disagree.
     *
     * @param  \App\Models\Admin|\App\Models\User  $account
     * @param  string  $readRoute  The panel's own route for opening one notification
     * @return array<string, mixed>
     */
    public function bell($account, string $readRoute): array
    {
        return [
            'label'       => $account->unreadNotifications()->count(),
            'label_color' => 'danger',
            'dropdown'    => view('notifications.dropdown', [
                'notifications' => $account->notifications()->latest()->limit(self::BELL_LIMIT)->get(),
                'readRoute'     => $readRoute,
            ])->render(),
        ];
    }

    /**
     * Hand a notification to each recipient.
     *
     * @param  \Illuminate\Support\Collection  $recipients
     * @return int
     */
    private function send($recipients, ThhaNotification $notification): int
    {
        $sent = 0;

        foreach ($recipients as $recipient) {
            $recipient->notify($notification);
            $sent++;
        }

        return $sent;
    }
}
