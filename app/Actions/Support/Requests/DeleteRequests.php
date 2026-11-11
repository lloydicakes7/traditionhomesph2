<?php

namespace App\Actions\Support\Requests;

use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Services\ActivityLogService;

class DeleteRequests
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
            'request',
            'Deleted ' . count($ids) . ' requests'
        );

        RequestModel::whereIn('id', $ids)->delete();

        return count($ids);
    }
}
