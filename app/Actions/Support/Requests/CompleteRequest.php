<?php

namespace App\Actions\Support\Requests;

use App\Enums\RequestStatus;
use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Notifications\RequestDecidedNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Validation\ValidationException;

class CompleteRequest
{
    private ActivityLogService $activity;

    private NotificationService $notifications;

    public function __construct(ActivityLogService $activity, NotificationService $notifications)
    {
        $this->activity = $activity;
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    public function handle(RequestModel $request, Admin $by): RequestModel
    {
        $this->refuseUnlessApproved($request);

        $request->update([
            'status'                => RequestStatus::COMPLETED,
            'completed_by_admin_id' => $by->id,
            'completed_at'          => now(),
        ]);

        $this->activity->log(
            $by,
            'completed',
            'request',
            "Completed the {$request->type_label} request for {$request->resident_label}",
            ['request_id' => $request->id]
        );

        $request = $request->fresh(['resident', 'processedByAdmin', 'completedByAdmin']);

        $this->notifications->toHomeowner(
            $request->resident ? $request->resident->user : null,
            new RequestDecidedNotification($request)
        );

        return $request;
    }

    // ===== Helpers =====

    private function refuseUnlessApproved(RequestModel $request): void
    {
        if ($request->status && $request->status->isApproved()) {
            return;
        }

        throw ValidationException::withMessages([
            'request' => 'Only an approved request can be completed.',
        ]);
    }
}
