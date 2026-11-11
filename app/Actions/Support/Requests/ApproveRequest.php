<?php

namespace App\Actions\Support\Requests;

use App\Actions\Support\Requests\Concerns\DecidesRequest;
use App\Enums\RequestStatus;
use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class ApproveRequest
{
    use DecidesRequest;

    private NotificationService $notifications;

    private ActivityLogService $activity;

    public function __construct(NotificationService $notifications, ActivityLogService $activity)
    {
        $this->notifications = $notifications;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(RequestModel $request, array $data, Admin $by): RequestModel
    {
        return $this->decide($request, RequestStatus::APPROVED(), $this->remarks($data), $by);
    }

    // ===== Helpers =====

    private function remarks(array $data): ?string
    {
        $remarks = trim((string) ($data['remarks'] ?? ''));

        return $remarks === '' ? null : $remarks;
    }
}
