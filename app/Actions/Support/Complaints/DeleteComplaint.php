<?php

namespace App\Actions\Support\Complaints;

use App\Models\Admin;
use App\Models\Complaint;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;

class DeleteComplaint
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Complaint $complaint, Admin $by): void
    {
        $this->activity->log(
            $by,
            'deleted',
            'complaint',
            "Deleted complaint \"{$complaint->subject}\"",
            ['complaint_id' => $complaint->id]
        );

        foreach ($complaint->attachments as $attachment) {
            StorageHelper::deletePrivate($attachment->path);
        }

        $complaint->delete();
    }
}
