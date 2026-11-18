<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Household\HouseholdLinkRequest;
use App\Models\User;

class HouseholdLinkDecided extends OfficerNotification
{
    private $link;

    private $head;

    public function __construct(HouseholdLinkRequest $link, User $head)
    {
        $this->link = $link;
        $this->head = $head;
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
        return ($this->approved() ? 'Household head approved ' : 'Household head rejected ')
            . $this->residentName();
    }

    protected function body($notifiable): string
    {
        return $this->head->name
            . ($this->approved() ? ' approved ' : ' rejected ')
            . $this->residentName()
            . ' as a member of the household at '
            . $this->unitLabel() . '.';
    }

    protected function url($notifiable): ?string
    {
        return route('homeowners.profile-verifications');
    }

    private function approved(): bool
    {
        return (bool) $this->link->status?->isApproved();
    }

    private function residentName(): string
    {
        return $this->link->resident?->full_name ?? 'A resident';
    }

    private function unitLabel(): string
    {
        return $this->link->household?->property?->block_lot_label ?? 'the household';
    }
}
