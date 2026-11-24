<?php

namespace App\Actions\Support\Complaints;

use App\Models\Admin;
use App\Models\Complaint;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;
use Illuminate\Validation\ValidationException;

class DeleteComplaint
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Complaint $complaint, ?Admin $by = null): void
    {
        if ($by === null && ! $complaint->can_be_changed_by_homeowner) {
            throw ValidationException::withMessages([
                'delete' => 'This complaint can no longer be deleted: the office has already acted on it.',
            ]);
        }

        if ($by !== null) {
            $this->activity->log(
                $by,
                'deleted',
                'complaint',
                "Deleted complaint \"{$complaint->subject}\"",
                ['complaint_id' => $complaint->id]
            );
        }

        foreach ($complaint->attachments as $attachment) {
            StorageHelper::deletePrivate($attachment->path);
        }

        $complaint->delete();
    }
}
