<?php

namespace App\Actions\Support\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Admin;
use App\Models\Complaint;
use App\Models\Resident;
use App\Notifications\ComplaintFiledNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class RecordComplaint
{
    private NotificationService $notifications;

    private ActivityLogService $activity;

    public function __construct(NotificationService $notifications, ActivityLogService $activity)
    {
        $this->notifications = $notifications;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Complaint
    {
        $resident = Resident::findOrFail((int) $data['resident_id']);

        $complaint = Complaint::create([
            'resident_id'       => $resident->id,
            'filed_by_admin_id' => $by->id,
            'submitter_name'    => $resident->full_name,
            'category'          => $data['category'],
            'subject'           => $data['subject'],
            'description'       => $data['description'],
            'status'            => ComplaintStatus::PENDING,
        ]);

        $this->activity->log(
            $by,
            'created',
            'complaint',
            "Filed complaint \"{$complaint->subject}\" for {$resident->full_name}",
            ['complaint_id' => $complaint->id]
        );

        $complaint = $complaint->fresh(['resident', 'filedByAdmin']);

        $this->notifications->toOfficers(new ComplaintFiledNotification($complaint), [$by]);

        return $complaint;
    }
}
