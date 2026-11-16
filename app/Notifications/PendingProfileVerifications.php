<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;

class PendingProfileVerifications extends OfficerNotification
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

    protected function title($notifiable): string
    {
        return $this->count === 1
            ? '1 homeowner account is waiting for verification'
            : $this->count . ' homeowner accounts are waiting for verification';
    }

    protected function body($notifiable): string
    {
        return 'Open the verification queue to review the submitted profiles.';
    }

    protected function url($notifiable): ?string
    {
        return route('homeowners.profile-verifications');
    }
}
