<?php

namespace App\Notifications;

use App\Enums\NotificationType;

/**
 * The first thing an officer account is told: it has one.
 *
 * Written by the Admin observer the moment the account exists
 * (App\Observers\AdminObserver) — whether the office created it or the officer
 * registered themselves — so a new officer's bell is never empty and always
 * says the same thing.
 */
class AdminWelcomeNotification extends ThhaNotification
{
    public function type(): NotificationType
    {
        return NotificationType::WELCOME();
    }

    protected function title($notifiable): string
    {
        return 'Welcome to the ' . config('thha.hoa_short_name') . ' office panel';
    }

    protected function body($notifiable): string
    {
        return 'Your account is ready. The sidebar lists the pages your role allows, and this bell keeps what '
            . 'happens on them — start at the Dashboard.';
    }

    protected function url($notifiable): ?string
    {
        return route('admin.home');
    }
}
