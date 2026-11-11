<?php

namespace App\Actions\Support\Violations;

use App\Models\Admin;
use App\Models\Violation;
use App\Services\ActivityLogService;

class DeleteViolations
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
            'violation',
            'Deleted ' . count($ids) . ' violations'
        );

        Violation::whereIn('id', $ids)->delete();

        return count($ids);
    }
}
