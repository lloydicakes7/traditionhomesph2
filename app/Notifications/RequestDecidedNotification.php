<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Request as RequestModel;

/**
 * The homeowner's own request was answered: the office approved, completed or
 * cancelled it.
 *
 * Sent from the Actions that move a request along, so the homeowner learns the
 * outcome wherever the decision was taken — the office's list or the portal.
 * Filed-by-the-homeowner filing raises no notification for them: they did it,
 * and the office's answer is the news.
 */
class RequestDecidedNotification extends ThhaNotification
{
    /** @var \App\Models\Request */
    private $request;

    public function __construct(RequestModel $request)
    {
        $this->request = $request;
    }

    public function type(): NotificationType
    {
        return NotificationType::REQUEST();
    }

    protected function title($notifiable): string
    {
        return $this->request->status_label . ' — ' . $this->request->type_label . ' request';
    }

    protected function body($notifiable): string
    {
        $remarks = trim((string) $this->request->remarks);

        if ($this->request->is_approved) {
            return 'The office approved your request. Download the transaction slip and bring it to the HOA office.';
        }

        if ($this->request->is_completed) {
            return 'The office completed your request — the document has been released.';
        }

        return $remarks !== ''
            ? 'The office cancelled your request: ' . $remarks
            : 'The office cancelled your request — open it for their remarks.';
    }

    protected function url($notifiable): ?string
    {
        return route('portal.requests');
    }
}
