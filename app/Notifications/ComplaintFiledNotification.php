<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Complaint;

/**
 * A complaint was filed — something for the support desk to answer.
 *
 * Filed by a homeowner or logged by the office on their behalf; either way it
 * goes only to the officers whose role grants Complaints, and never back to the
 * officer who logged it (the support desk's own work is not news to them — see
 * NotificationService::toOfficers).
 */
class ComplaintFiledNotification extends OfficerNotification
{
    /** @var \App\Models\Complaint */
    private $complaint;

    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    public function type(): NotificationType
    {
        return NotificationType::COMPLAINT();
    }

    public function governedBy(): array
    {
        return ['viewAny', Complaint::class];
    }

    protected function title($notifiable): string
    {
        return 'Complaint filed — ' . $this->complaint->category_label;
    }

    protected function body($notifiable): string
    {
        return 'From ' . $this->complaint->submitter_label . ' · waiting for a response.';
    }

    protected function url($notifiable): ?string
    {
        return route('support.complaints');
    }
}
