<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Choose your password" — mailed to an account the office opened or reset.
 *
 * It is mail only: the signed link is a secret and is never written to the
 * notifications table, so it cannot be read back from the bell.
 */
class SetPasswordNotification extends Notification
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
            ->subject(__('auth.set_password_subject'))
            ->line(__('auth.set_password_intro'))
            ->action(__('auth.set_password_action'), $this->url)
            ->line(__('auth.set_password_expiry'))
            ->line(__('auth.set_password_ignore'));
    }
}
