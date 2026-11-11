<?php

namespace App\Actions\Support\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintFiledNotification;
use App\Services\NotificationService;

class FileComplaint
{
    private NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    public function handle(array $data, User $account): Complaint
    {
        $resident = $account->resident;

        $complaint = Complaint::create([
            'resident_id'    => $account->resident_id,
            'submitter_name' => $resident->full_name ?? null,
            'category'       => $data['category'],
            'subject'        => $data['subject'],
            'description'    => $data['description'],
            'status'         => ComplaintStatus::PENDING,
        ]);

        $complaint = $complaint->fresh('resident');

        $this->notifications->toOfficers(new ComplaintFiledNotification($complaint));

        return $complaint;
    }
}
