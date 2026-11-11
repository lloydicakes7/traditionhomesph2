<?php

namespace App\Notifications;

use App\Enums\NotificationType;

/**
 * The first thing a portal account is told: it has one.
 *
 * A homeowner never registers — the office issues the account — so this is the
 * only welcome they get, and it is the one notification an account receives
 * about something nobody did to them: the account's own creation. It is
 * written by the User observer the moment the account exists
 * (App\Observers\UserObserver).
 */
class HomeownerWelcomeNotification extends ThhaNotification
{
    public function type(): NotificationType
    {
        return NotificationType::WELCOME();
    }

    protected function title($notifiable): string
    {
        return 'Welcome to the ' . config('thha.hoa_short_name') . ' portal';
    }

    protected function body($notifiable): string
    {
        return 'Your account is ready. Your bookings, requests, complaints and statement of account are in '
            . 'the sidebar, together with the association\'s announcements.';
    }

    protected function url($notifiable): ?string
    {
        return route('portal.dashboard');
    }
}
