<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The office opened a self-registered account.
 *
 * It is mailed as well as kept for the bell: the applicant cannot see the bell
 * until they have signed in, so the letter has to tell them they may.
 */
class RegistrationApprovedNotification extends ThhaNotification
{
    /** @var \App\Models\User */
    private $account;

    public function __construct(User $account)
    {
        $this->account = $account;
    }

    public function type(): NotificationType
    {
        return NotificationType::WELCOME();
    }

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    protected function title($notifiable): string
    {
        return 'Your registration was approved';
    }

    protected function body($notifiable): string
    {
        return 'Your account is open. Sign in with the password you chose.';
    }

    protected function url($notifiable): ?string
    {
        return route('portal.dashboard');
    }

    /**
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Your Tradition Homes registration was approved')
            ->line('The office has approved the registration for ' . $this->account->email . '.')
            ->line('Sign in at ' . route('login') . ' with this email address and the password you chose.');
    }
}
