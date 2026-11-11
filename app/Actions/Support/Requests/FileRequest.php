<?php

namespace App\Actions\Support\Requests;

use App\Actions\Support\Requests\Concerns\RequestPayload;
use App\Enums\RequestStatus;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Notifications\RequestFiledNotification;
use App\Services\NotificationService;

class FileRequest
{
    use RequestPayload;

    private NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    public function handle(array $data, User $account): RequestModel
    {
        $this->ensureMembershipProfileReady($data, $account->resident_id);

        $request = RequestModel::create(array_merge($this->payload($data), [
            'resident_id' => $account->resident_id,
            'status'      => RequestStatus::PENDING,
        ]));

        $request = $request->fresh('resident');

        $this->notifications->toOfficers(new RequestFiledNotification($request));

        return $request;
    }
}
