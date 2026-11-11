<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;

/**
 * A homeowner registered themselves and is waiting for the office.
 *
 * The bell it opens is the Homeowners list, so only an officer whose role may
 * view that list is told — the same ability the page is gated on.
 */
class HomeownerRegisteredNotification extends OfficerNotification
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
        return ['viewAny', User::class];
    }

    protected function title($notifiable): string
    {
        return 'New homeowner registration — ' . $this->account->name;
    }

    protected function body($notifiable): string
    {
        $property = $this->account->resident?->household?->property;

        return ($property ? $property->phase_block_lot_label . ' · ' : '')
            . 'Waiting for the office to approve the account.';
    }

    protected function url($notifiable): ?string
    {
        return route('homeowners.pending-registrations');
    }
}
