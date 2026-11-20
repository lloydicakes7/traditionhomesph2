<?php

namespace App\Actions\Support\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintFiledNotification;
use App\Services\NotificationService;
use App\Services\Support\ComplaintAttachmentService;
use Illuminate\Support\Facades\DB;

class FileComplaint
{
    private NotificationService $notifications;

    private ComplaintAttachmentService $attachments;

    public function __construct(
        NotificationService $notifications,
        ComplaintAttachmentService $attachments
    ) {
        $this->notifications = $notifications;
        $this->attachments = $attachments;
    }

    // ===== Handle =====

    public function handle(array $data, User $account, array $attachments = []): Complaint
    {
        $resident = $account->resident;

        $complaint = DB::transaction(function () use ($data, $account, $resident, $attachments) {
            $complaint = Complaint::create([
                'resident_id'       => $account->resident_id,
                'submitter_name'    => $resident->full_name ?? null,
                'category'          => $data['category'],
                'other_description' => Complaint::otherDescriptionFrom($data),
                'subject'           => $data['subject'],
                'description'       => $data['description'],
                'status'            => ComplaintStatus::PENDING,
            ]);

            $this->attachments->store($complaint, $attachments);

            return $complaint;
        });

        $complaint = $complaint->fresh('resident');

        $this->notifications->toOfficers(new ComplaintFiledNotification($complaint));

        return $complaint;
    }
}
