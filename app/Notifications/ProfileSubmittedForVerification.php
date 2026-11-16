<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;

/**
 * A resident finished the onboarding form and is waiting for the office.
 *
 * The bell it opens is the verification queue, so only an officer whose role may
 * open that page is told.
 */
class ProfileSubmittedForVerification extends OfficerNotification
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

    public function governedBy(): array
    {
        return ['viewProfileVerifications', User::class];
    }

    protected function title($notifiable): string
    {
        return 'Profile submitted for verification — ' . $this->account->name;
    }

    protected function body($notifiable): string
    {
        $property = $this->account->resident?->household?->property;

        return ($property ? $property->phase_block_lot_label . ' · ' : '')
            . 'Waiting for the office to check the details and the address.';
    }

    protected function url($notifiable): ?string
    {
        return route('homeowners.profile-verifications');
    }
}
