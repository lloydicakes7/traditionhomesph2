<?php

namespace App\Actions\Support\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Admin;
use App\Models\Complaint;
use App\Notifications\ComplaintRespondedNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Validation\ValidationException;

class RespondToComplaint
{
    private NotificationService $notifications;

    private ActivityLogService $activity;

    public function __construct(NotificationService $notifications, ActivityLogService $activity)
    {
        $this->notifications = $notifications;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Complaint $complaint, array $data, Admin $by): Complaint
    {
        $status = ComplaintStatus::tryFrom($data['responseStatus'] ?? '') ?: ComplaintStatus::IN_PROGRESS();

        $this->refuse($complaint, $status);

        $complaint->update([
            'response'              => $data['response'],
            'status'                => $status,
            'responded_by_admin_id' => $by->id,
            'responded_at'          => now(),
            'closed_at'             => $status->isFinal() ? now() : null,
        ]);

        $this->activity->log(
            $by,
            'updated',
            'complaint',
            "Updated complaint \"{$complaint->subject}\" to {$status->label()}",
            ['complaint_id' => $complaint->id]
        );

        $complaint = $complaint->fresh(['resident', 'filedByAdmin', 'respondedByAdmin']);

        $this->notifications->toHomeowner(
            $complaint->resident ? $complaint->resident->user : null,
            new ComplaintRespondedNotification($complaint)
        );

        return $complaint;
    }

    // ===== Helpers =====

    private function refuse(Complaint $complaint, ComplaintStatus $status): void
    {
        if (! $complaint->can_be_responded_to) {
            throw ValidationException::withMessages([
                'responseStatus' => 'This complaint is closed — it takes no further response.',
            ]);
        }

        if ($complaint->status && $complaint->status->isFinal() && ! $status->isFinal()) {
            throw ValidationException::withMessages([
                'responseStatus' => 'A ' . mb_strtolower($complaint->status->label())
                    . ' complaint cannot go back to ' . mb_strtolower($status->label()) . '.',
            ]);
        }
    }
}
