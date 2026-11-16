<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The office checked the resident's profile and asked for a correction.
 *
 * The note is the officer's own words, shown once on the onboarding form and
 * kept in this message, so the resident knows what to fix.
 */
class ProfileRejectedNotification extends ThhaNotification
{
    /** @var \App\Models\User */
    private $account;

    /** @var string */
    private $note;

    public function __construct(User $account, string $note)
    {
        $this->account = $account;
        $this->note = $note;
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
            ->subject('Your Tradition Homes profile needs a correction')
            ->greeting('Hello ' . ($this->account->resident?->first_name ?? 'Homeowner') . ',')
            ->line('The office checked your profile and asked for a correction.')
            ->line('What to fix: ' . $this->note)
            ->action('Open your profile', route('login'));
    }

    protected function title($notifiable): string
    {
        return 'Your profile needs a correction';
    }

    protected function body($notifiable): string
    {
        return $this->note;
    }

    protected function url($notifiable): ?string
    {
        return route('portal.onboarding');
    }
}
