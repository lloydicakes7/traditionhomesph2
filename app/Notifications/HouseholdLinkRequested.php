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
        return (new MailMessage())
            ->subject('A resident has requested to join your household')
            ->greeting('Greetings, ' . ($this->link->headAccount()?->resident?->first_name ?? 'Homeowner') . ',')
            ->line($this->residentName() . ' listed your unit at ' . $this->unitLabel() . ' as their address.')
            ->line('They requested to be recorded as ' . ($this->link->relationship ?: 'a member') . ' of your household.')
            ->line('Kindly confirm whether this person belongs to your household, or decline the request.')
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
