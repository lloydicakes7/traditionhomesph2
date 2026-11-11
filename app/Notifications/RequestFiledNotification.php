<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Request as RequestModel;

/**
 * A request was filed — something for the support desk to decide on.
 *
 * Written from the one place a request is filed
 * (App\Actions\Support\Requests\FileRequest), and only sent to the
 * officers whose role grants Requests: the page it opens is gated on exactly
 * that, so nobody is told about work they cannot do.
 */
class RequestFiledNotification extends OfficerNotification
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

    public function governedBy(): array
    {
        return ['viewAny', RequestModel::class];
    }

    protected function title($notifiable): string
    {
        return 'Request filed — ' . $this->request->type_label;
    }

    protected function body($notifiable): string
    {
        return $this->request->resident_label . ' · ' . $this->request->summary;
    }

    protected function url($notifiable): ?string
    {
        return route('support.requests');
    }
}
