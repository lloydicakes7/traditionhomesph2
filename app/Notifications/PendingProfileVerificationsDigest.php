<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class PendingProfileVerificationsDigest extends OfficerNotification
{
    private $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function type(): NotificationType
    {
        return NotificationType::HOMEOWNER();
    }

    public function governedBy(): array
    {
        return ['viewProfileVerifications', User::class];
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->template('emails.notification')
            ->subject('Homeowner accounts are waiting for verification')
            ->greeting('Greetings, ' . $notifiable->name . ',')
            ->line($this->summary())
            ->line('Kindly review the queue so each account can be opened without further delay.')
            ->action('Open the verification queue', route('homeowners.profile-verifications'));
    }

    protected function title($notifiable): string
    {
        return 'Homeowner accounts waiting for verification';
    }

    protected function body($notifiable): string
    {
        return $this->summary();
    }

    protected function url($notifiable): ?string
    {
        return route('homeowners.profile-verifications');
    }

    private function summary(): string
    {
        return $this->count === 1
            ? 'There is 1 homeowner account that is still waiting for verification.'
            : 'There are ' . $this->count . ' homeowner accounts that are still waiting for verification.';
    }
}
