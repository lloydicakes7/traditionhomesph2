<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Your officer account is ready" — mailed once, when the office opens an
 * officer's account. The link in it is the same one the reset flow uses; only
 * the wording is a welcome rather than a notice that a reset was requested.
 *
 * It is mail only: the link is a secret and is never written to the
 * notifications table, so it cannot be read back from the bell.
 */
class OfficerAccountOpened extends Notification
{
    use Queueable;

    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    // ===== Channels =====

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('auth.officer_welcome_subject'))
            ->greeting(__('auth.officer_welcome_greeting', ['name' => $notifiable->name]))
            ->line(__('auth.officer_welcome_intro'))
            ->action(__('auth.officer_welcome_action'), $this->url)
            ->line(__('auth.set_password_expiry'))
            ->line(__('auth.set_password_ignore'));
    }
}
