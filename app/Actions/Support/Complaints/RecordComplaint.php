<?php

namespace App\Actions\Support\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Admin;
use App\Models\Complaint;
use App\Models\Resident;
use App\Notifications\ComplaintFiledNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\Support\ComplaintAttachmentService;
use Illuminate\Support\Facades\DB;

class RecordComplaint
{
    private NotificationService $notifications;

    private ActivityLogService $activity;

    private ComplaintAttachmentService $attachments;

    public function __construct(
        NotificationService $notifications,
        ActivityLogService $activity,
        ComplaintAttachmentService $attachments
    ) {
        $this->notifications = $notifications;
        $this->activity = $activity;
        $this->attachments = $attachments;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by, array $attachments = []): Complaint
    {
        $resident = Resident::findOrFail((int) $data['resident_id']);

        $complaint = DB::transaction(function () use ($data, $by, $resident, $attachments) {
            $complaint = Complaint::create([
                'resident_id'       => $resident->id,
                'filed_by_admin_id' => $by->id,
                'submitter_name'    => $resident->full_name,
                'category'          => $data['category'],
                'other_description' => Complaint::otherDescriptionFrom($data),
                'subject'           => $data['subject'],
                'description'       => $data['description'],
                'status'            => ComplaintStatus::PENDING,
            ]);

            $this->attachments->store($complaint, $attachments);

            return $complaint;
        });

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
