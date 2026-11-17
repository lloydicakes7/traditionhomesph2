<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Household\HouseholdLinkRequest;
use Illuminate\Notifications\Messages\MailMessage;

class HouseholdLinkRequested extends ThhaNotification
{
    private $link;

    public function __construct(HouseholdLinkRequest $link)
    {
        $this->link = $link;
    }

    public function type(): NotificationType
    {
        return NotificationType::HOMEOWNER();
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirm a resident joining your household')
            ->greeting('Hello ' . ($this->link->headAccount()?->resident?->first_name ?? 'Homeowner') . ',')
            ->line($this->residentName() . ' named your unit at ' . $this->unitLabel() . ' as their address.')
            ->line('They asked to be part of your household as ' . ($this->link->relationship ?: 'a member') . '.')
            ->line('Please confirm whether they belong to your household, or reject the request.')
            ->action('Review the request', route('portal.household-requests'));
    }

    protected function title($notifiable): string
    {
        return 'A resident asked to join your household';
    }

    protected function body($notifiable): string
    {
        return $this->residentName() . ' asked to join your household at ' . $this->unitLabel() . '.';
    }

    protected function url($notifiable): ?string
    {
        return route('portal.household-requests');
    }

    private function residentName(): string
    {
        return $this->link->resident?->full_name ?? 'A resident';
    }

    private function unitLabel(): string
    {
        return $this->link->household?->property?->block_lot_label ?? 'your unit';
    }
}
