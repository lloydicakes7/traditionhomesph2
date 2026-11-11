<?php

namespace App\Actions\Support\Requests;

use App\Actions\Support\Requests\Concerns\RequestPayload;
use App\Enums\RequestStatus;
use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Notifications\RequestFiledNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class FileRequestForResident
{
    use RequestPayload;

    private NotificationService $notifications;

    private ActivityLogService $activity;

    public function __construct(NotificationService $notifications, ActivityLogService $activity)
    {
        $this->notifications = $notifications;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): RequestModel
    {
        $this->ensureMembershipProfileReady($data, (int) $data['resident_id']);

        $request = RequestModel::create(array_merge($this->payload($data), [
            'resident_id' => (int) $data['resident_id'],
            'status'      => RequestStatus::PENDING,
        ]));

        $this->activity->log(
            $by,
            'created',
            'request',
            "Filed {$request->type_label} request for {$request->resident_label}",
            ['request_id' => $request->id]
        );

        $request = $request->fresh(['resident', 'processedByAdmin']);

        $this->notifications->toOfficers(new RequestFiledNotification($request), [$by]);

        return $request;
    }
}
