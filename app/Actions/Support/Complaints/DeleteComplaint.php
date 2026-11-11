<?php

namespace App\Actions\Support\Complaints;

use App\Models\Admin;
use App\Models\Complaint;
use App\Services\ActivityLogService;

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

        $complaint->delete();
    }
}
