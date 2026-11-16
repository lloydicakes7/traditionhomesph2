<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The office checked the resident's profile, the unit and the household.
 *
 * Mailed as well as kept for the bell: it is the message that says the portal is
 * open to them now.
 */
class ProfileVerifiedNotification extends ThhaNotification
{
    /** @var \App\Models\User */
    private $account;

    public function __construct(User $account)
    {
        $this->account = $account;
    }

    public function type(): NotificationType
    {
        return NotificationType::HOMEOWNER();
    }

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Tradition Homes profile is verified')
            ->greeting('Hello ' . ($this->account->resident?->first_name ?? 'Homeowner') . ',')
            ->line('The office checked your personal information, your unit and your household.')
            ->line('Your portal account now has every service open to it.')
            ->action('Open the portal', route('login'));
    }

    protected function title($notifiable): string
    {
        return 'Your profile is verified';
    }

    protected function body($notifiable): string
    {
        return 'The office accepted your details and your household. The portal services are open.';
    }

    protected function url($notifiable): ?string
    {
        return route('portal.dashboard');
    }
}
