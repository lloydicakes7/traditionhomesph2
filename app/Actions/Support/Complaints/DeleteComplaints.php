<?php

namespace App\Actions\Support\Complaints;

use App\Models\Admin;
use App\Models\Complaint;
use App\Services\ActivityLogService;

class DeleteComplaints
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $ids = array_values(array_filter($ids));

        if ($ids === []) {
            return 0;
        }

        $this->activity->log(
            $by,
            'deleted',
            'complaint',
            'Deleted ' . count($ids) . ' complaints'
        );

        Complaint::whereIn('id', $ids)->delete();

        return count($ids);
    }
}
