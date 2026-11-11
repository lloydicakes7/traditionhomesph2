<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Complaint;

/**
 * The office answered a complaint the homeowner filed.
 *
 * Sent from the one place a complaint is answered
 * (App\Actions\Support\Complaints\RespondToComplaint) — a homeowner who files a
 * complaint is never told about the filing itself; the officer's reply is what
 * they are waiting for.
 */
class ComplaintRespondedNotification extends ThhaNotification
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

    protected function title($notifiable): string
    {
        return 'Complaint ' . strtolower($this->complaint->status_label) . ' — ' . $this->complaint->category_label;
    }

    protected function body($notifiable): string
    {
        return 'The office has answered your complaint. Open it to read the response.';
    }

    protected function url($notifiable): ?string
    {
        return route('portal.complaints');
    }
}
